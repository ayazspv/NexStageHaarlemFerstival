<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount } from "vue";
import { useForm } from "@inertiajs/vue3";
import { Editor, EditorContent } from "@tiptap/vue-3";
import StarterKit from "@tiptap/starter-kit";
import Image from "@tiptap/extension-image";
import TextStyle from "@tiptap/extension-text-style";
import Color from "@tiptap/extension-color";
import Heading from "@tiptap/extension-heading";
import ListItem from "@tiptap/extension-list-item";
import Dropcursor from "@tiptap/extension-dropcursor";
import Paragraph from "@tiptap/extension-paragraph";
import Document from "@tiptap/extension-document";
import Text from "@tiptap/extension-text";
import AdminAppLayout from "../Layouts/AdminAppLayout.vue";

const form = useForm({
    name: "",
    date: "",
    link: "",
    description: "",
    images: [],
});

// Initialize TipTap editor
const editor = ref<Editor | undefined>();

onMounted(() => {
    editor.value = new Editor({
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
        content: `
            <h2>Welcome to the Event Editor</h2>
            <p>You can format text using <strong>bold</strong>, <em>italic</em>, or <u>underline</u>.</p>
            <p>Click "Add Image" to insert an image.</p>
            <img src="https://placehold.co/600x400" />
            <p>Drag images around inside the editor.</p>
        `,
        onUpdate: ({ editor }) => {
            form.description = editor.getHTML();
        },
    });
});

onBeforeUnmount(() => {
    if (editor.value) editor.value.destroy();
});

// Add Image from URL
const addImage = () => {
    const url = window.prompt("Enter image URL");

    if (url && editor.value) {
        editor.value.chain().focus().setImage({ src: url }).run();
    }
};

// Handle Image Upload from File
const handleImageUpload = (event: Event) => {
    const input = event.target as HTMLInputElement;
    if (!input.files || input.files.length === 0) return;

    const file = input.files[0];
    const reader = new FileReader();
    reader.onload = () => {
        const result = reader.result as string;
        form.images.push(result);
        editor.value?.chain().focus().setImage({ src: result }).run();
    };
    reader.readAsDataURL(file);
};

// Submit Event Data
const submitEvent = () => {
    form.post("/admin/events", {
        onSuccess: () => {
            alert("Event added successfully!");
            form.reset();
            editor.value?.commands.setContent(""); // Clear editor
        },
    });
};
</script>

<template>
    <AdminAppLayout title="Events">
        <div class="container">
            <h1>Create New Event</h1>

            <!-- Event Name -->
            <label>Event Name:</label>
            <input v-model="form.name" type="text" class="form-control" placeholder="Enter event name" />

            <!-- Event Date -->
            <label>Event Date:</label>
            <input v-model="form.date" type="date" class="form-control" />

            <!-- Event Link -->
            <label>Event Link:</label>
            <input v-model="form.link" type="text" class="form-control" placeholder="External event link (optional)" />

            <!-- Image Upload -->
            <label>Upload Image:</label>
            <input type="file" class="form-control" @change="handleImageUpload" />

            <!-- WYSIWYG Editor Toolbar -->
            <div v-if="editor" class="editor-toolbar">
                <button @click="editor.chain().focus().toggleBold().run()" :class="{ 'is-active': editor.isActive('bold') }">
                    Bold
                </button>
                <button @click="editor.chain().focus().toggleItalic().run()" :class="{ 'is-active': editor.isActive('italic') }">
                    Italic
                </button>
                <button @click="editor.chain().focus().toggleHeading({ level: 1 }).run()" :class="{ 'is-active': editor.isActive('heading', { level: 1 }) }">
                    H1
                </button>
                <button @click="editor.chain().focus().toggleHeading({ level: 2 }).run()" :class="{ 'is-active': editor.isActive('heading', { level: 2 }) }">
                    H2
                </button>
                <button @click="editor.chain().focus().toggleBulletList().run()" :class="{ 'is-active': editor.isActive('bulletList') }">
                    Bullet List
                </button>
                <button @click="editor.chain().focus().toggleOrderedList().run()" :class="{ 'is-active': editor.isActive('orderedList') }">
                    Ordered List
                </button>
                <button @click="editor.chain().focus().toggleBlockquote().run()" :class="{ 'is-active': editor.isActive('blockquote') }">
                    Blockquote
                </button>
                <button @click="editor.chain().focus().toggleCodeBlock().run()" :class="{ 'is-active': editor.isActive('codeBlock') }">
                    Code Block
                </button>
                <button @click="addImage">
                    Add Image from URL
                </button>
            </div>

            <!-- WYSIWYG Editor -->
            <EditorContent :editor="editor" class="editor" />

            <!-- Save Button -->
            <button @click="submitEvent" class="btn btn-primary mt-3">Save Event</button>
        </div>
    </AdminAppLayout>
</template>

<style scoped>
.container {
    max-width: 800px;
    margin: auto;
    padding: 20px;
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
img {
    display: block;
    height: auto;
    margin: 1.5rem 0;
    max-width: 100%;
}
.ProseMirror-selectednode {
    outline: 3px solid #aaa;
}
</style>
