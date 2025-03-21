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
    // This is populated recursively from the server (or by adding new subpages)
    children?: CmsPage[];
}

const props = defineProps<{
    festival: Festival;
    parentPage: CmsPage;
}>();

const page = usePage();
const csrfToken = (page.props.csrf_token as string) || '';

/*
  A single “CMS box” represents the current page being edited.
  This code initializes a Tiptap editor for that page.
*/
type CmsBox = {
    id?: number;
    editor: Editor;
    content: string;
    isSaved: boolean;
    title: string;
    parent_id?: number | null;
};

const cmsBox = ref<CmsBox | null>(null);

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
      <p>You can format text using <strong>bold</strong>, <em>italic</em> or <u>underline</u>.</p>
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
        id,
        editor,
        content: editor.getHTML(),
        isSaved,
        title: initialTitle,
        parent_id,
    };
};

onMounted(() => {
    // Initialize the editor with the page content coming from the server.
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

onBeforeUnmount(() => {
    if (cmsBox.value) {
        cmsBox.value.editor.destroy();
    }
});

const addImage = () => {
    const url = window.prompt('Enter image URL');
    if (url && cmsBox.value) {
        cmsBox.value.editor.chain().focus().setImage({ src: url }).run();
    }
};

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

const submitCms = () => {
    if (!cmsBox.value) return;

    const pageData = {
        id: cmsBox.value.id || null,
        title: cmsBox.value.title,
        content: cmsBox.value.content,
        // The parent_id of this page remains unchanged.
        parent_id: props.parentPage.parent_id,
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

/*
  createSubpage:
  Always create a new subpage for the current page.
  The API will return the new subpage id.
*/
const createSubpage = async () => {
    if (!cmsBox.value || !cmsBox.value.isSaved || !cmsBox.value.id) {
        alert('You must save this page before adding a subpage.');
        return;
    }

    try {
        const { data } = await axios.get(
            `/admin/festivals/cms/create-subpage/${props.festival.id}/${cmsBox.value.id}`
        );
        if (data.success && data.subpageId) {
            // Update the local children array so the UI can reflect the new subpage immediately.
            if (!props.parentPage.children) {
                props.parentPage.children = [];
            }
            props.parentPage.children.push({
                id: data.subpageId,
                title: 'New Subpage',
                content: '',
                parent_id: cmsBox.value.id,
                children: [],
            });
            // Redirect to the subpage editor.
            Inertia.visit(`/admin/festivals/cms/edit-subpage/${props.festival.id}/${data.subpageId}`);
        } else {
            alert('Failed to create subpage. No subpageId returned.');
        }
    } catch (error) {
        console.error('Error creating subpage:', error);
        alert('Failed to create subpage. Check console for details.');
    }
};

/*
  manageSubpage:
  Redirects to the subpage editor for an existing subpage.
*/
const manageSubpage = (subpage: CmsPage) => {
    Inertia.visit(`/admin/festivals/cms/edit-subpage/${props.festival.id}/${subpage.id}`);
};
</script>

<template>
    <AdminAppLayout title="Manage Subpage">
        <div class="container">
            <h1>Manage Subpage for {{ props.festival.name }}</h1>

            <!-- Recursive Subpages Section -->
            <div class="subpages-section">
                <h2>Subpages</h2>
                <div v-if="props.parentPage.children && props.parentPage.children.length">
                    <SubpageTree
                        v-for="child in props.parentPage.children"
                        :key="child.id"
                        :subpage="child"
                        :festival-id="props.festival.id"
                        @manageSubpage="manageSubpage"
                    />
                </div>
                <button @click="createSubpage" class="btn btn-secondary mt-3">
                    Add Subpage
                </button>
            </div>

            <!-- Festival image -->
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

            <!-- Editor for current page -->
            <div v-if="cmsBox" class="cms-box mt-4 mb-5">
                <input
                    type="text"
                    v-model="cmsBox.title"
                    placeholder="Enter page title"
                    class="form-control mb-2"
                    required
                />

                <div class="editor-toolbar">
                    <button
                        @click="cmsBox.editor.chain().focus().toggleBold().run()"
                        :class="{ 'is-active': cmsBox.editor.isActive('bold') }"
                    >
                        Bold
                    </button>
                    <button @click="addImage">
                        Add Image from URL
                    </button>
                </div>

                <EditorContent :editor="cmsBox.editor" class="editor" />

                <div class="mt-3 float-end">
                    <button
                        type="button"
                        v-if="cmsBox.isSaved && cmsBox.id"
                        @click="createSubpage"
                        class="btn btn-secondary btn-md mr-4"
                    >
                        Add New Subpage
                    </button>
                </div>
            </div>

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
.subpages-section {
    margin-top: 2rem;
    padding: 1rem;
    border-top: 1px solid #ddd;
}
</style>
