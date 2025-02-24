<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount } from 'vue';
import { useForm, router, usePage } from '@inertiajs/vue3';
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
    content: string;
    parent_id?: number | null;
}

const props = defineProps<{
    festival: Festival;
    parentPage: CmsPage;
}>();

const page = usePage();
const csrfToken = (page.props.csrf_token as string) || '';

// Define a type for each editor box.
type CmsBox = {
    id?: number;
    editor: Editor;
    content: string;
    isSaved: boolean;
    title: string;
    parent_id?: number | null;
};

const cmsBox = ref<CmsBox | null>(null);

// Create a new CMS box (for an existing page or a new one).
const createCmsBox = (
    initialContent: string = '',
    isSaved: boolean = false,
    initialTitle: string = '',
    id?: number,
    parent_id: number | null = null
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
            if (cmsBox.value) {
                cmsBox.value.content = editor.getHTML();
            }
        },
    });

    cmsBox.value = {
        id: id,
        editor,
        content: editor.getHTML(),
        isSaved,
        title: initialTitle,
        parent_id: parent_id,
    };
};

// When mounted, load the saved CMS page (if any).
onMounted(() => {
    if (props.parentPage) {
        createCmsBox(
            props.parentPage.content,
            true,
            props.parentPage.title,
            props.parentPage.id,
            props.parentPage.parent_id
        );
    } else {
        createCmsBox();
    }
});

// Clean up editors on unmount.
onBeforeUnmount(() => {
    if (cmsBox.value) {
        cmsBox.value.editor.destroy();
    }
});

// Insert an image by URL into the editor.
const addImage = () => {
    const url = window.prompt('Enter image URL');
    if (url && cmsBox.value) {
        cmsBox.value.editor.chain().focus().setImage({ src: url }).run();
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
        if (cmsBox.value) {
            cmsBox.value.editor.chain().focus().setImage({ src: result }).run();
        }
    };
    reader.readAsDataURL(file);
};

// On submission, gather all page data and send it to your update route.
const submitCms = () => {
    if (!cmsBox.value) return;

    const pageData = {
        id: cmsBox.value.id || null,
        title: cmsBox.value.title,
        content: cmsBox.value.content,
        parent_id: props.parentPage.id,
    };

    useForm(pageData).patch(`/admin/festivals/cms/edit-subpage/${props.festival.id}/${cmsBox.value.id}`, {
        headers: { 'X-CSRF-TOKEN': csrfToken },
        onSuccess: () => {
            alert('Content updated successfully!');
        },
        onError: (errors) => {
            console.error('Error updating CMS:', errors);
            alert('Failed to update CMS content. Please check the console for details.');
        },
    });
};
</script>

<template>
    <AdminAppLayout title="Manage Content">
        <div class="container">
            <h1>Manage Subpage for {{ props.festival.name }}</h1>

            <!-- Festival image (if exists) -->
            <div v-if="props.festival.image" class="mb-3">
                <img
                    :src="`/storage/${props.festival.image}`"
                    alt="Festival Image"
                    class="img-thumbnail"
                    style="height: 100px"
                />
            </div>

            <!-- Image Upload -->
            <label class="form-label">Upload Image:</label>
            <input type="file" class="form-control" @change="handleImageUpload" />

            <!-- Loop over and render each CMS box -->
            <div v-if="cmsBox" class="cms-box mt-4 mb-5">
                <!-- Title input -->
                <input
                    type="text"
                    v-model="cmsBox.title"
                    placeholder="Enter page title"
                    class="form-control mb-2"
                    required
                />

                <!-- Editor toolbar -->
                <div class="editor-toolbar">
                    <button @click="cmsBox.editor.chain().focus().toggleBold().run()"
                            :class="{ 'is-active': cmsBox.editor.isActive('bold') }">
                        Bold
                    </button>
                    <button @click="cmsBox.editor.chain().focus().toggleItalic().run()"
                            :class="{ 'is-active': cmsBox.editor.isActive('italic') }">
                        Italic
                    </button>
                    <button @click="cmsBox.editor.chain().focus().toggleHeading({ level: 1 }).run()"
                            :class="{ 'is-active': cmsBox.editor.isActive('heading', { level: 1 }) }">
                        H1
                    </button>
                    <button @click="cmsBox.editor.chain().focus().toggleHeading({ level: 2 }).run()"
                            :class="{ 'is-active': cmsBox.editor.isActive('heading', { level: 2 }) }">
                        H2
                    </button>
                    <button @click="cmsBox.editor.chain().focus().toggleBulletList().run()"
                            :class="{ 'is-active': cmsBox.editor.isActive('bulletList') }">
                        Bullet List
                    </button>
                    <button @click="cmsBox.editor.chain().focus().toggleOrderedList().run()"
                            :class="{ 'is-active': cmsBox.editor.isActive('orderedList') }">
                        Ordered List
                    </button>
                    <button @click="cmsBox.editor.chain().focus().toggleBlockquote().run()"
                            :class="{ 'is-active': cmsBox.editor.isActive('blockquote') }">
                        Blockquote
                    </button>
                    <button @click="cmsBox.editor.chain().focus().toggleCodeBlock().run()"
                            :class="{ 'is-active': cmsBox.editor.isActive('codeBlock') }">
                        Code Block
                    </button>
                    <button @click="addImage">
                        Add Image from URL
                    </button>
                </div>

                <!-- The TipTap editor content -->
                <EditorContent :editor="cmsBox.editor as Editor" class="editor" />
            </div>

            <!-- Save/Update Button -->
            <button @click="submitCms" class="btn btn-primary mt-3">
                Save changes
            </button>
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
</style>
