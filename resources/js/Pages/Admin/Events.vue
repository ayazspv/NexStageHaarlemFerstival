<script setup lang="ts">

import {Editor, EditorContent} from "@tiptap/vue-3";
import AdminAppLayout from "../Layouts/AdminAppLayout.vue";
import {useForm} from "@inertiajs/vue3";
import {ref} from "vue";
import StarterKit from "@tiptap/starter-kit";
import Image from "@tiptap/extension-image";

const form = useForm({
    name: "",
    description: "",
    link: "",
    image: "",
});

// TipTap WYSIWYG Editor
const editor = ref(
    new Editor({
        extensions: [StarterKit, Image],
        content: "",
        onUpdate: ({ editor }) => {
            form.description = editor.getHTML();
        },
    })
);

// Image Upload Function
const handleImageUpload = (event) => {
    const file = event.target.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = () => {
        form.image = reader.result;
        editor.value.chain().focus().setImage({ src: reader.result }).run();
    };
    reader.readAsDataURL(file);
};

// Submit Event Data
const submitEvent = () => {
    form.post("/admin/events", {
        onSuccess: () => {
            alert("Event added successfully!");
            form.reset();
            editor.value.commands.setContent(""); // Clear editor
        },
    });
};
</script>

<template>
    <AdminAppLayout title="Admin">
        <div class="container">
            <h1>Manage Haarlem Festival Events</h1>

            <label>Name:</label>
            <input v-model="form.name" type="text" class="form-control" />

            <label>Event Link:</label>
            <input v-model="form.link" type="text" class="form-control" />

            <label>Event Image:</label>
            <input type="file" class="form-control" @change="handleImageUpload" />

            <label>Description:</label>
            <EditorContent :editor="editor" class="editor" />

            <button @click="submitEvent" class="btn btn-primary mt-3">
                Save Event
            </button>
        </div>
    </AdminAppLayout>
</template>

<style scoped>

</style>
