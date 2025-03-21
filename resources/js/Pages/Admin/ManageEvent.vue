<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import { Editor, EditorContent } from '@tiptap/vue-3';
import StarterKit from '@tiptap/starter-kit';
import Image from '@tiptap/extension-image';
import TextStyle from '@tiptap/extension-text-style';
import Color from '@tiptap/extension-color';
import Heading from '@tiptap/extension-heading';
import ListItem from '@tiptap/extension-list-item';
import Dropcursor from '@tiptap/extension-dropcursor';
import Paragraph from '@tiptap/extension-paragraph';
import Document from '@tiptap/extension-document';
import Text from '@tiptap/extension-text';
import AdminAppLayout from '../Layouts/AdminAppLayout.vue';
import { Festival } from '../../../models';
import { Inertia } from '@inertiajs/inertia';
import SubpageTree from "../Components/SubpageTree.vue";

// Define your interface for a CMS page
interface CmsPage {
    id?: number;
    title: string;
    content: string;
    parent_id?: number | null;
    // The server may return "children"; we store them locally as "subpages" or children
    subpages?: CmsPage[];
    children?: CmsPage[]; // In case your backend uses "children"
}

const props = defineProps<{
    festival: Festival;
    cmsPages: CmsPage[];
}>();

// We retrieve the CSRF token
const page = usePage();
const csrfToken = (page.props.csrf_token as string) || '';

// Each editor box represents one top-level page in the ManageEvent view.
type CmsBox = {
    id?: number;
    editor: Editor;
    content: string;
    isSaved: boolean;
    title: string;
    parent_id: number | null;
    creatingSubpage?: boolean;
    subpages?: CmsPage[]; // local subpages array
};

const cmsBoxes = ref<CmsBox[]>([]);

// Create an editor box for a top-level CMS page
const createCmsBox = (
    initialContent: string = '',
    isSaved: boolean = false,
    initialTitle: string = '',
    id?: number,
    parent_id: number | null = null,
    subpages: CmsPage[] = []
) => {
    const editor = new Editor({
        extensions: [
            StarterKit,
            Document,
            Paragraph,
            Text,
            Image,
            Dropcursor,
            TextStyle,
            Color.configure({ types: [TextStyle.name, ListItem.name] }),
            Heading.configure({ levels: [1, 2, 3, 4, 5, 6] }),
            ListItem,
        ],
        content: initialContent || `
      <h2>Welcome to the Event Editor</h2>
      <p>You can format text using <strong>bold</strong>, <em>italic</em>, or <u>underline</u>.</p>
      <p>Click "Add Image" to insert an image.</p>
      <img src="https://placehold.co/600x400" />
      <p>Drag images around inside the editor.</p>
    `,
        onUpdate: ({ editor }) => {
            const box = cmsBoxes.value.find((b) => b.editor === editor as Editor);
            if (box) {
                box.content = editor.getHTML();
            }
        },
    });

    cmsBoxes.value.push({
        id,
        editor,
        content: editor.getHTML(),
        isSaved,
        title: initialTitle,
        parent_id,
        subpages
    });
};

// On mount, we load all top-level pages (parent_id === null) into separate editor boxes
onMounted(() => {
    if (props.cmsPages && props.cmsPages.length > 0) {
        props.cmsPages.forEach((cms) => {
            // In case content is an array, pick the first string
            const initialContent = Array.isArray(cms.content) ? cms.content[0] : cms.content;

            // If it's top-level, create a box
            if (cms.parent_id === null) {
                createCmsBox(
                    initialContent,
                    true,
                    cms.title,
                    cms.id,
                    cms.parent_id ?? null,
                    // Some backends store children in "children", others in "subpages"
                    cms.children || cms.subpages || []
                );
            }
        });
    } else {
        // If no top-level pages exist, start with one empty editor box
        createCmsBox();
    }
});

// Destroy editors on unmount
onBeforeUnmount(() => {
    cmsBoxes.value.forEach((box) => {
        box.editor.destroy();
    });
});

// Add a new top-level CMS box
const addNewBox = () => {
    createCmsBox();
};

// Remove a CMS box
const removeCmsBox = (index: number) => {
    const box = cmsBoxes.value[index];
    if (box.isSaved && box.id) {
        Inertia.patch(
            `/admin/festivals/cms/${props.festival.id}/remove-content`,
            { cms_id: box.id },
            {
                headers: { 'X-CSRF-TOKEN': csrfToken },
                onSuccess: () => {
                    box.editor.destroy();
                    cmsBoxes.value.splice(index, 1);
                },
                onError: () => {
                    alert('Failed to remove content from the database.');
                },
            }
        );
    } else {
        box.editor.destroy();
        cmsBoxes.value.splice(index, 1);
    }
};

// Insert an image into the last editor box
const addImage = () => {
    const url = window.prompt('Enter image URL');
    if (url && cmsBoxes.value.length > 0) {
        const lastBox = cmsBoxes.value[cmsBoxes.value.length - 1];
        lastBox.editor.chain().focus().setImage({ src: url }).run();
    }
};

// Handle image file uploads
const handleImageUpload = (event: Event) => {
    const input = event.target as HTMLInputElement;
    if (!input.files || input.files.length === 0) return;
    const file = input.files[0];
    const reader = new FileReader();
    reader.onload = () => {
        const result = reader.result as string;
        if (cmsBoxes.value.length > 0) {
            const lastBox = cmsBoxes.value[cmsBoxes.value.length - 1];
            lastBox.editor.chain().focus().setImage({ src: result }).run();
        }
    };
    reader.readAsDataURL(file);
};

// Clicking "Add or Edit Subpage" navigates to EditSubpage.vue for that box
const addOrEditSubpage = (box: CmsBox) => {
    if (!box.isSaved || !box.id) {
        alert('You must save this page before adding a subpage.');
        return;
    }
    Inertia.visit(`/admin/festivals/cms/edit-subpage/${props.festival.id}/${box.id}`);
};

// Manage an existing sub-subpage (passed in from SubpageTree)
const manageSubpage = (subpage: CmsPage) => {
    Inertia.visit(`/admin/festivals/cms/edit-subpage/${props.festival.id}/${subpage.id}`);
};

// Save all top-level pages
const submitCms = () => {
    if (cmsBoxes.value.some(box => !box.title || box.title.trim() === '')) {
        alert('Title cannot be empty.');
        return;
    }

    const pages = cmsBoxes.value.map((box) => ({
        id: box.id || null,
        title: box.title,
        content: box.content,
        parent_id: null,
    }));

    useForm({ pages }).patch(`/admin/festivals/cms/${props.festival.id}`, {
        headers: { 'X-CSRF-TOKEN': csrfToken },
        onSuccess: () => {
            alert('Content updated successfully!');
            location.reload();
        },
        onError: (errors) => {
            if (typeof errors === 'object') {
                const messages = Object.values(errors).flat().join('\n');
                alert(`Failed to update CMS content:\n${messages}`);
            } else {
                alert(`Failed to update CMS content:\n${errors}`);
            }
        },
    });
};

const urlFriendly = (title: string) => {
    return '/' + title
        .trim()
        .toLowerCase()
        .replace(/\s+/g, '-'); // convert spaces to dashes
}

const parseDirectory = (title: string) => {
    return `${urlFriendly(props.festival.name)}${urlFriendly(title)}`;
}
</script>

<template>
    <AdminAppLayout title="Manage Content">
        <div class="container">
            <h1>Manage Content for {{ props.festival.name }}</h1>

            <!-- 1) DISPLAY THE FULL SUBPAGE TREE AT THE TOP -->
            <div v-if="props.cmsPages && props.cmsPages.length" class="subpage-tree-container mb-4">
                <h5>{{urlFriendly(props.festival.name)}}</h5>
                <div
                    v-for="page in props.cmsPages"
                    :key="page.id"
                    class="mt-2"
                >
                    <SubpageTree
                        :subpage="page"
                        @manageSubpage="manageSubpage"
                    />
                </div>
            </div>

            <!-- 2) FESTIVAL IMAGE -->
            <div v-if="props.festival.image" class="mb-3">
                <img
                    :src="`/storage/${props.festival.image}`"
                    alt="Festival Image"
                    class="img-thumbnail"
                    style="height: 100px"
                />
            </div>

            <!-- 3) IMAGE UPLOAD -->
            <label class="form-label">Upload Image:</label>
            <input type="file" class="form-control" @change="handleImageUpload" />

            <!-- 4) ADD NEW TOP-LEVEL CONTENT BOX -->
            <button type="button" @click="addNewBox" class="btn btn-primary mt-4">
                Add New Content Box
            </button>

            <!-- 5) RENDER EACH CMS BOX -->
            <div
                v-for="(box, index) in cmsBoxes"
                :key="index"
                class="cms-box mt-4 mb-5"
            >
                <p>Path: <strong>{{ parseDirectory(box.title) }}</strong></p>
                <input
                    type="text"
                    v-model="box.title"
                    placeholder="Enter page title"
                    class="form-control mb-2"
                    required
                />

                <!-- Editor toolbar -->
                <div v-if="box.editor" class="editor-toolbar">
                    <button type="button" @click="box.editor.chain().focus().toggleBold().run()" :class="{ 'is-active': box.editor.isActive('bold') }">Bold</button>
                    <button type="button" @click="box.editor.chain().focus().toggleItalic().run()" :class="{ 'is-active': box.editor.isActive('italic') }">Italic</button>
                    <button type="button" @click="box.editor.chain().focus().toggleHeading({ level: 1 }).run()" :class="{ 'is-active': box.editor.isActive('heading', { level: 1 }) }">H1</button>
                    <button type="button" @click="box.editor.chain().focus().toggleHeading({ level: 2 }).run()" :class="{ 'is-active': box.editor.isActive('heading', { level: 2 }) }">H2</button>
                    <button type="button" @click="box.editor.chain().focus().toggleBulletList().run()" :class="{ 'is-active': box.editor.isActive('bulletList') }">Bullet List</button>
                    <button type="button" @click="box.editor.chain().focus().toggleOrderedList().run()" :class="{ 'is-active': box.editor.isActive('orderedList') }">Ordered List</button>
                    <button type="button" @click="box.editor.chain().focus().toggleBlockquote().run()" :class="{ 'is-active': box.editor.isActive('blockquote') }">Blockquote</button>
                    <button type="button" @click="box.editor.chain().focus().toggleCodeBlock().run()" :class="{ 'is-active': box.editor.isActive('codeBlock') }">Code Block</button>
                    <button type="button" @click="addImage">Add Image from URL</button>
                </div>

                <!-- Editor content -->
                <EditorContent :editor="box.editor as Editor" class="editor" />

                <!-- Action buttons -->
                <div class="mt-3 float-end">
                    <button
                        type="button"
                        v-if="box.isSaved && box.id"
                        @click="addOrEditSubpage(box as CmsBox)"
                        class="btn btn-secondary btn-md mr-4"
                    >
                        <span v-if="box.subpages && box.subpages.length > 0">
                            Edit a Subpage
                        </span>
                        <span v-else>
                            Add a Subpage
                        </span>
                    </button>
                    <button
                        type="button"
                        @click="removeCmsBox(index)"
                        class="btn delete-btn btn-md"
                    >
                        Remove This Content Box
                    </button>
                </div>
            </div>

            <!-- 6) SAVE/UPDATE BUTTON -->
            <div class="d-flex row save-container">
                <button type="button" @click="submitCms" class="btn btn-primary mt-3">
                    Save changes
                </button>
                <small>*Save changes to add a subpage</small>
            </div>
        </div>
    </AdminAppLayout>
</template>

<style scoped>
.container {
    margin: 20px;
}
.editor-toolbar {
    display: flex;
    flex-wrap: wrap;
    gap: 5px;
    margin-bottom: 10px;
}
.editor-toolbar button {
    padding: 5px 10px;
    border: none;
    background: #f5f5f5;
    cursor: pointer;
    border-radius: 4px;
}
.editor-toolbar button:hover {
    background: #ddd;
}
.editor-toolbar .is-active {
    background: #bbb;
}
.editor {
    border: 1px solid #ccc;
    min-height: 300px;
    padding: 10px;
    background: white;
}
.cms-box {
    margin-bottom: 2rem;
    border: 1px solid #eee;
    padding: 10px;
    border-radius: 4px;
    position: relative;
}
.delete-btn {
    background-color: red;
    color: white;
}
.save-container {
    width: 125px;
}
.subpage-tree-container {
    margin-top: 15px;
    padding: 10px;
    border: 1px dashed #ccc;
    border-radius: 4px;
}
</style>
