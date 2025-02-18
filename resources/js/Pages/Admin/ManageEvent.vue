<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount } from "vue";
import {useForm, router, usePage} from "@inertiajs/vue3";
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
import {Festival} from "../../../models";


const props = defineProps<{
    festival: Festival;
    cmsContents: string[];
}>();

const page = usePage();
const csrfToken = page.props.csrf_token as string || "";

const form = useForm({
    id: props.festival.id,
    name: props.festival.name,
    date: props.festival.date || "",
    link: props.festival.link || "",
    description: props.festival.description || "",
    images: [] as File[],
    contents: [] as string[],
});

type CmsBox = {
    editor: Editor;
    content: string;
    isSaved: boolean;
};

const cmsBoxes = ref<CmsBox[]>([]);

const createCmsBox = (initialContent: string = "", isSaved: boolean = false) => {
    const newEditor = new Editor({
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
            const box = cmsBoxes.value.find((box) => box.editor === editor as Editor);
            if (box) {
                box.content = editor.getHTML();
            }
        },
    });
    cmsBoxes.value.push({
        editor: newEditor,
        content: newEditor.getHTML(),
        isSaved,
    });
};

// On mount, load the saved content panels from the festival.
// cmsContents is passed in from the controller (cmsManage).
onMounted(() => {
    if (props.cmsContents && Array.isArray(props.cmsContents) && props.cmsContents.length > 0) {
        props.cmsContents.forEach((content: string) => {
            createCmsBox(content, true);
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

const addNewBox = () => {
    createCmsBox();
};

// Function to remove a CMS panel.
// If the panel was already saved in the DB, send a patch request to remove it.
const removeCmsBox = (index: number) => {
    const box = cmsBoxes.value[index];

    if (box.isSaved) {
        router.patch(`/admin/festivals/cms/${props.festival.id}/remove-content`, { index }, {
            headers: {
                "X-CSRF-TOKEN": csrfToken,
            },
            onSuccess: () => {
                box.editor.destroy();
                cmsBoxes.value.splice(index, 1);
            },
            onError: () => {
                alert("Failed to remove content from the database.");
            },
        });
    } else {
        box.editor.destroy();
        cmsBoxes.value.splice(index, 1);
        console.log("CMS boxes after removal:", cmsBoxes.value);
    }
};

// Function to add an image from URL into the last CMS panel.
const addImage = () => {
    const url = window.prompt("Enter image URL");
    if (url && cmsBoxes.value.length > 0) {
        const lastBox = cmsBoxes.value[cmsBoxes.value.length - 1];
        lastBox.editor.chain().focus().setImage({ src: url }).run();
    }
};


const handleImageUpload = (event: Event) => {
    const input = event.target as HTMLInputElement;
    if (!input.files || input.files.length === 0) return;

    form.images.push(input.files[0]);

    const reader = new FileReader();
    reader.onload = () => {
        const result = reader.result as string;
        if (cmsBoxes.value.length > 0) {
            const lastBox = cmsBoxes.value[cmsBoxes.value.length - 1];
            lastBox.editor.chain().focus().setImage({ src: result }).run();
        }
    };
    reader.readAsDataURL(input.files[0]);
};

// When submitting, gather content from all panels and update the festival via cmsUpdate.
const submitCms = () => {
    form.contents = cmsBoxes.value.map((box) => box.content);

    form.patch(`/admin/festivals/cms/${props.festival.id}`, {
        headers: {
            "X-CSRF-TOKEN": csrfToken,
        },
        onSuccess: () => {
            alert("Content updated successfully!");
        },
    });
};
</script>

<template>
    <AdminAppLayout title="Manage Content">
        <div class="container">
            <h1>Manage Content for {{ props.festival.name }}</h1>

            <!-- Optional: Display the festival's current image -->
            <div v-if="props.festival.image" class="mb-3">
                <img :src="`/storage/${props.festival.image}`" alt="Festival Image" class="img-thumbnail" style="height: 100px">
            </div>

            <!-- File Upload for inserting an image into the CMS editor -->
            <label class="form-label">Upload Image:</label>
            <input type="file" class="form-control" @change="handleImageUpload" />

            <!-- Button to add a new CMS content box -->
            <button @click="addNewBox" class="btn btn-primary mt-4">
                Add New Content Box
            </button>

            <!-- Render each CMS Editor Panel -->
            <div v-for="(box, index) in cmsBoxes" :key="index" class="cms-box mt-4">
                <!-- Remove button for each panel -->
                <button @click="removeCmsBox(index)" class="btn btn-danger btn-sm mb-2">
                    Remove This Content Box
                </button>

                <!-- Toolbar for this CMS panel -->
                <div v-if="box.editor" class="editor-toolbar">
                    <button @click="box.editor.chain().focus().toggleBold().run()" :class="{ 'is-active': box.editor.isActive('bold') }">
                        Bold
                    </button>
                    <button @click="box.editor.chain().focus().toggleItalic().run()" :class="{ 'is-active': box.editor.isActive('italic') }">
                        Italic
                    </button>
                    <button @click="box.editor.chain().focus().toggleHeading({ level: 1 }).run()" :class="{ 'is-active': box.editor.isActive('heading', { level: 1 }) }">
                        H1
                    </button>
                    <button @click="box.editor.chain().focus().toggleHeading({ level: 2 }).run()" :class="{ 'is-active': box.editor.isActive('heading', { level: 2 }) }">
                        H2
                    </button>
                    <button @click="box.editor.chain().focus().toggleBulletList().run()" :class="{ 'is-active': box.editor.isActive('bulletList') }">
                        Bullet List
                    </button>
                    <button @click="box.editor.chain().focus().toggleOrderedList().run()" :class="{ 'is-active': box.editor.isActive('orderedList') }">
                        Ordered List
                    </button>
                    <button @click="box.editor.chain().focus().toggleBlockquote().run()" :class="{ 'is-active': box.editor.isActive('blockquote') }">
                        Blockquote
                    </button>
                    <button @click="box.editor.chain().focus().toggleCodeBlock().run()" :class="{ 'is-active': box.editor.isActive('codeBlock') }">
                        Code Block
                    </button>
                    <button @click="addImage">
                        Add Image from URL
                    </button>
                </div>

                <!-- The TipTap Editor Content -->
                <EditorContent :editor="box.editor as Editor" class="editor" />
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
img {
    display: block;
    height: auto;
    margin: 1.5rem 0;
    max-width: 100%;
}
.ProseMirror-selectednode {
    outline: 3px solid #aaa;
}
.cms-box {
    margin-bottom: 2rem;
    border: 1px solid #eee;
    padding: 10px;
    border-radius: 4px;
    position: relative;
}
.cms-box button.btn-danger {
    position: absolute;
    top: 5px;
    right: 5px;
}
</style>
