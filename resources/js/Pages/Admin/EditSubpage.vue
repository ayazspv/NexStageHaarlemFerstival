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
import SubpageTree from '../Components/SubpageTree.vue';
import { Festival } from '../../../models';
import { Inertia } from '@inertiajs/inertia';
import axios from 'axios';

interface CmsPage {
    id?: number;
    title: string;
    content: string;
    parent_id?: number | null;
    // This is populated recursively from the server or via local additions.
    children?: CmsPage[];
}

const props = defineProps<{
    festival: Festival;
    parentPage: CmsPage;
}>();

// Get CSRF token.
const page = usePage();
const csrfToken = (page.props.csrf_token as string) || '';

// A CMS box represents a subpage of the current parent page.
type CmsBox = {
    id?: number;
    editor: Editor;
    content: string;
    isSaved: boolean;
    title: string;
    // All boxes created here will share the same parent (props.parentPage.id)
    parent_id: number | null;
    // Optionally, a box may have its own nested subpages
    subpages?: CmsPage[];
};

const cmsBoxes = ref<CmsBox[]>([]);

// Create a new CMS box for a subpage.
const createCmsBox = (
    initialContent: string = '',
    isSaved: boolean = false,
    initialTitle: string = '',
    id?: number,
    // By default, assign the parent page’s id as the parent_id.
    parent_id: number | null = props.parentPage.id,
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
      <h2>Enter Subpage Content</h2>
      <p>Start typing…</p>
      <img src="https://placehold.co/600x400" />
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
        subpages,
    });
};

onMounted(() => {
    // Load existing subpages (children of the current parent page) into multiple boxes.
    if (props.parentPage.children && props.parentPage.children.length > 0) {
        props.parentPage.children.forEach((child) => {
            // In case the content is stored as an array, take the first element.
            const initialContent = Array.isArray(child.content) ? child.content[0] : child.content;
            createCmsBox(initialContent, true, child.title, child.id, props.parentPage.id, child.children || []);
        });
    }
    // Optionally, if no subpages exist, you might choose to start with one blank box.
    // else {
    //   createCmsBox();
    // }
});

onBeforeUnmount(() => {
    cmsBoxes.value.forEach((box) => {
        box.editor.destroy();
    });
});

// Add a new content box (i.e. a new subpage editor) at the current level.
const addNewBox = () => {
    createCmsBox();
};

// Remove a content box.
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

const addImage = () => {
    const url = window.prompt('Enter image URL');
    if (url && cmsBoxes.value.length > 0) {
        // Insert image into the last editor box.
        const lastBox = cmsBoxes.value[cmsBoxes.value.length - 1];
        lastBox.editor.chain().focus().setImage({ src: url }).run();
    }
};

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

// When clicking "Add/Edit Nested Subpages" on a box, redirect to that box’s own editor view.
const manageSubpage = (subpage: CmsPage) => {
    Inertia.visit(`/admin/festivals/cms/edit-subpage/${props.festival.id}/${subpage.id}`);
};

// Save all subpages (content boxes) at this level.
// All boxes here represent subpages of props.parentPage.
const submitCms = () => {
    if (cmsBoxes.value.some((box) => !box.title || box.title.trim() === '')) {
        alert('Title cannot be empty.');
        return;
    }

    const pages = cmsBoxes.value.map((box) => ({
        id: box.id || null,
        title: box.title,
        content: box.content,
        parent_id: props.parentPage.id,
    }));
    useForm({ pages }).patch(`/admin/festivals/cms/${props.festival.id}`, {
        headers: { 'X-CSRF-TOKEN': csrfToken },
        onSuccess: () => {
            alert('Subpages updated successfully!');
            location.reload();
        },
        onError: (errors) => {
            if (typeof errors === 'object') {
                const messages = Object.values(errors).flat().join('\n');
                alert(`Failed to update subpages:\n${messages}`);
            } else {
                alert(`Failed to update subpages:\n${errors}`);
            }
        },
    });
};
</script>

<template>
    <AdminAppLayout title="Manage Subpages">
        <div class="container">
            <h1>Manage Subpages for "{{ props.parentPage.title }}"</h1>

            <!-- Recursive tree display for nested subpages -->
            <div class="subpages-section mt-4 mb-4">
                <div v-if="props.parentPage.children && props.parentPage.children.length">
                    <SubpageTree
                        v-for="child in props.parentPage.children"
                        :key="child.id"
                        :subpage="child"
                        :festival-id="props.festival.id"
                        @manageSubpage="manageSubpage"
                    />
                </div>
            </div>

            <!-- Image Upload for all boxes -->
            <label class="form-label">Upload Image:</label>
            <input type="file" class="form-control" @change="handleImageUpload" />

            <!-- Button to add a new subpage (content box) -->
            <button type="button" @click="addNewBox" class="btn btn-primary mt-4">
                Add New Content Box
            </button>

            <!-- Loop over and render each CMS box (each representing one subpage) -->
            <div v-for="(box, index) in cmsBoxes" :key="index" class="cms-box mt-4 mb-5">
                <!-- Title input -->
                <p><strong>Displayed as:</strong> {{ box.title }}</p>
                <input type="text" v-model="box.title" placeholder="Enter subpage title" class="form-control mb-2" required />

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
                    <!-- Button to manage nested subpages for this subpage -->
                    <button type="button"
                            v-if="box.isSaved && box.id"
                            @click="manageSubpage({ id: box.id, title: box.title, content: box.content, parent_id: box.parent_id, children: box.subpages })"
                            class="btn btn-secondary btn-md mr-4">
                        Manage Nested Subpages
                    </button>
                    <!-- Remove this content box -->
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
                <small>*Save changes to update subpages</small>
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
