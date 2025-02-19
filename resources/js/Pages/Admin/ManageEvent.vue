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

interface CmsPage {
    id?: number;
    title: string;
    content: string
    parent_id?: number | null;
    subpages?: CmsPage[];
}

const props = defineProps<{
    festival: Festival;
    cmsPages: CmsPage[];
}>();

// Get CSRF token.
const page = usePage();
const csrfToken = (page.props.csrf_token as string) || '';

// Define a type for each editor box on the Manage page.
type CmsBox = {
    id?: number;
    editor: Editor;
    content: string;
    isSaved: boolean;
    title: string;
    parent_id: number | null;
    creatingSubpage?: boolean;
    subpages?: CmsPage[];
};

const cmsBoxes = ref<CmsBox[]>([]);

// Function to create an editor box for a top-level CMS record.
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
        id: id,
        editor,
        content: editor.getHTML(),
        isSaved,
        title: initialTitle,
        parent_id: parent_id,
        subpages: subpages,
    });
};

// On mount, load only top-level pages (those with parent_id === null).
onMounted(() => {
    if (props.cmsPages && props.cmsPages.length > 0) {
        props.cmsPages.forEach((cms) => {
            const initialContent = Array.isArray(cms.content) ? cms.content[0] : cms.content;
            if (cms.parent_id === null) {
                createCmsBox(
                    initialContent,
                    true,
                    cms.title,
                    cms.id,
                    cms.parent_id ?? null,
                    cms.subpages || []
                );
            }
        });
    } else {
        createCmsBox();
    }
});

onBeforeUnmount(() => {
    cmsBoxes.value.forEach((box) => {
        box.editor.destroy();
    });
});

// Add a new top-level CMS box.
const addNewBox = () => {
    createCmsBox();
};

// Remove a CMS box.
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

// Helper function to insert an image into the last editor.
const addImage = () => {
    const url = window.prompt('Enter image URL');
    if (url && cmsBoxes.value.length > 0) {
        const lastBox = cmsBoxes.value[cmsBoxes.value.length - 1];
        lastBox.editor.chain().focus().setImage({ src: url }).run();
    }
};

// Handle image file uploads.
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

// When clicking "Add a Subpage":
// If this top-level page already has a subpage, show "Edit a Subpage" instead.
// Double requests needs to be fixed
const addOrEditSubpage = (box: CmsBox) => {
    if (!box.isSaved || !box.id) {
        alert('You must save this page before adding a subpage.');
        return;
    }

    const child = props.cmsPages.find(x => x.parent_id === box.id);
    if (child) {
        if (!child?.id) {
            alert('Subpage record not found.');
            return;
        }
        const url = `/admin/festivals/cms/edit-subpage/${props.festival.id}/${child.id}`;
        Inertia.visit(url);
    } else {

        if (box.creatingSubpage) return;
        box.creatingSubpage = true;
        const url = `/admin/festivals/cms/create-subpage/${props.festival.id}/${box.id}`;
        Inertia.visit(url);
    }
};

const submitCms = () => {
    const pages = cmsBoxes.value.map((box) => ({
        id: box.id || null,
        title: box.title,
        content: box.content,
        parent_id: null,
    }));
    useForm({ pages }).patch(`/admin/festivals/cms/${props.festival.id}`, {
        headers: { 'X-CSRF-TOKEN': csrfToken },
        onSuccess: () => alert('Content updated successfully!'),
        onError: (errors) => {
            console.error('Error updating CMS:', errors);
            alert('Failed to update CMS content. Check the console for details.');
        },
    });
};

console.log(cmsBoxes);
</script>

<template>
    <AdminAppLayout title="Manage Content">
        <div class="container">
            <h1>Manage Content for {{ props.festival.name }}</h1>

            <!-- Festival image -->
            <div v-if="props.festival.image" class="mb-3">
                <img :src="`/storage/${props.festival.image}`" alt="Festival Image" class="img-thumbnail" style="height: 100px" />
            </div>

            <!-- Image Upload -->
            <label class="form-label">Upload Image:</label>
            <input type="file" class="form-control" @change="handleImageUpload" />

            <!-- Button to add a new CMS box -->
            <button type="button" @click="addNewBox" class="btn btn-primary mt-4">
                Add New Content Box
            </button>

            <!-- Loop over and render each CMS box -->
            <div v-for="(box, index) in cmsBoxes" :key="index" class="cms-box mt-4 mb-5">
                <!-- Title input: change this to show an actual path -->
                <p><strong>Displayed as:</strong> {{ box.title }}</p>
                <input type="text" v-model="box.title" placeholder="Enter page title" class="form-control mb-2" required />

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

                <!-- The editor content -->
                <EditorContent :editor="box.editor as Editor" class="editor" />

                <!-- Action buttons -->
                <div class="mt-3 float-end">
                    <!-- Show "Edit Subpage" if one exists; otherwise show "Add a Subpage" -->
                    <button type="button"
                            v-if="box.isSaved && box.id"
                            @click="addOrEditSubpage(box as CmsBox)"
                            class="btn btn-secondary btn-md mr-4">
                        <template v-if="props.cmsPages.some(x => x.parent_id === box.id)">
                            Edit a Subpage
                        </template>
                        <template v-else>
                            Add a Subpage
                        </template>
                    </button>
                    <button type="button" @click="removeCmsBox(index)" class="btn delete-btn btn-md">
                        Remove This Content Box
                    </button>
                </div>
            </div>

            <!-- Save/Update Button -->
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
</style>
