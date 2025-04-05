<script setup lang="ts">
import {nextTick, onMounted, onUnmounted, reactive, ref} from 'vue';
import {Festival} from "../../../models";
import AppLayout from "@/Pages/Layouts/AppLayout.vue";
import {Inertia} from '@inertiajs/inertia';
import {usePage} from "@inertiajs/vue3";
import axios from "axios";

interface CmsPage {
    id?: number;
    title: string;
    content: string;
    parent_id?: number | null;
    children?: CmsPage[];
}

interface Props {
    festival: Festival;
    cmsPages: CmsPage[];
    currentPageId?: number;
}

const props = defineProps<Props>();

const selectedCmsId = ref<number | null>(
    props.currentPageId ?? (props.cmsPages && props.cmsPages.length ? props.cmsPages[0].id : null)
);
const parseTitle = (title: string) => {
    return title.trim().toLowerCase().replace(/\s+/g, '-');
};

const festivalSlug = parseTitle(props.festival.name);

const redirectToChildren = (page: CmsPage) => {
    const pageSlug = parseTitle(page.title);
    const basePath = `/festivals/${festivalSlug}`;
    let currentPath = window.location.pathname;
    let currentSuffix = currentPath.replace(new RegExp(`^${basePath}`), '');
    currentSuffix = currentSuffix.replace(/^\/|\/$/g, '');

    let newPath = currentSuffix === ''
        ? `${basePath}/${pageSlug}`
        : `${basePath}/${currentSuffix}/${pageSlug}`;

    Inertia.visit(newPath);
};

// -------------------------
// Modal & Style Management
// -------------------------
const isAdmin = ref(true);

const showStyleModal = ref(false);

const styleContent = ref('');

const editorRef = ref<HTMLTextAreaElement | null>(null);

const styleName = ref('');
const savedStyles = ref([]);
const selectedSavedStyle = ref<string>('');

const page = usePage();
const csrfToken = (page.props.csrf_token as string) || '';

/**
 * Inject or update a style tag with the provided CSS.
 */
const applyStyle = () => {
    let styleTag = document.getElementById('admin-dynamic-style');
    if (!styleTag) {
        styleTag = document.createElement('style');
        styleTag.id = 'admin-dynamic-style';
        document.head.appendChild(styleTag);
    }
    styleTag.innerHTML = styleContent.value;
};

// -------------------------
// Auto-Bracket, Auto-Indent & Auto-Tabbing | IDE-Like System
// -------------------------

const handleKeydown = (e: KeyboardEvent) => {
    const textarea = editorRef.value;
    if (!textarea) return;
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const value = styleContent.value;

    // Auto-close bracket: insert matching "}" after "{"
    if (e.key === '{') {
        e.preventDefault();
        styleContent.value = value.substring(0, start) + '{}' + value.substring(end);
        nextTick(() => {
            textarea.selectionStart = textarea.selectionEnd = start + 1;
        });
    }
    // Auto-indent on Enter.
    else if (e.key === 'Enter') {
        e.preventDefault();
        const beforeCursor = value.substring(0, start);
        const afterCursor = value.substring(start);

        const lastLineBreak = beforeCursor.lastIndexOf('\n');
        const currentLine = lastLineBreak === -1 ? beforeCursor : beforeCursor.substring(lastLineBreak + 1);
        const currentIndentMatch = currentLine.match(/^\s*/);
        const currentIndent = currentIndentMatch ? currentIndentMatch[0] : '';

        if (afterCursor.startsWith('}')) {
            const extraIndent = currentIndent + '    '; // 4 spaces added for inner content
            const insertion = '\n' + extraIndent + '\n' + currentIndent;
            styleContent.value = value.substring(0, start) + insertion + value.substring(start);
            nextTick(() => {
                textarea.selectionStart = textarea.selectionEnd = start + ('\n' + extraIndent).length;
            });
        } else {
            let indent = currentIndent;
            if (currentLine.trim().endsWith('{')) {
                indent += '    ';
            }
            const insertion = '\n' + indent;
            styleContent.value = value.substring(0, start) + insertion + value.substring(start);
            nextTick(() => {
                textarea.selectionStart = textarea.selectionEnd = start + insertion.length;
            });
        }
    }
    // Auto insert tab spaces when Tab key is pressed.
    else if (e.key === 'Tab') {
        e.preventDefault();
        const tabSpaces = '    '; // four spaces
        styleContent.value = value.substring(0, start) + tabSpaces + value.substring(end);
        nextTick(() => {
            textarea.selectionStart = textarea.selectionEnd = start + tabSpaces.length;
        });
    }
};

// -------------------------
// Modal Drag Logic
// -------------------------

const modalPosition = reactive({ x: 100, y: 100 });
let isDragging = false;
let dragOffset = { x: 0, y: 0 };

const startDrag = (e: MouseEvent) => {
    isDragging = true;
    dragOffset.x = e.clientX - modalPosition.x;
    dragOffset.y = e.clientY - modalPosition.y;
};

const onDrag = (e: MouseEvent) => {
    if (!isDragging) return;
    modalPosition.x = e.clientX - dragOffset.x;
    modalPosition.y = e.clientY - dragOffset.y;
};

const stopDrag = () => {
    isDragging = false;
};

onUnmounted(() => {
    window.removeEventListener('mousemove', onDrag);
    window.removeEventListener('mouseup', stopDrag);
});

// -------------------------
// Saved Style Functions
// -------------------------

const fetchSavedStyles = async () => {
    try {
        const response = await axios.get('/api/styles');
        console.log('API Response:', response.data);

        // Correctly access nested data array
        savedStyles.value = response.data.data; // Changed from response.data
        console.log('Processed styles:', savedStyles.value);

    } catch (error) {
        console.error('Error fetching styles:', error);
        savedStyles.value = [];
    }
};


const saveStyleToDb = () => {
    const payload = {
        cms_id: selectedCmsId.value,
        name: styleName.value,
        content: styleContent.value
    };

    Inertia.post('/admin/styles', payload, {
        headers: { 'X-CSRF-TOKEN': csrfToken },
        onSuccess: () => {
            styleName.value = '';
        }
    });
};
</script>

<template>
    <AppLayout :title="festival.name">
        <div class="festival-page">
            <h1>{{ festival.name }}</h1>

            <div v-if="cmsPages && cmsPages.length">
                <div v-for="(page, index) in cmsPages" :key="index">
                    <div class="content-box">
                        <div class="page-content" v-html="page.content"></div>
                        <div class="mt-3 mb-3 content-button" v-if="page.children && page.children?.length > 0">
                            <button class="btn btn-primary" @click="redirectToChildren(page)">Read more</button>
                        </div>
                    </div>
                </div>
            </div>
            <div v-else>
                <p>No CMS content available.</p>
            </div>
        </div>

        <!-- Draggable Modal for Admin Style Management -->
        <div
            v-if="showStyleModal"
            class="style-modal"
            :style="{ top: modalPosition.y + 'px', left: modalPosition.x + 'px' }">
            <div class="modal-header" @mousedown="startDrag">
                <span>Manage styles</span>
                <button class="close-btn" @click="showStyleModal = false">X</button>
            </div>
            <div class="modal-body">
                <!-- Style Name -->
                <div class="form-group">
                    <label for="styleName">Style Name:</label>
                    <input type="text" id="styleName" v-model="styleName" class="form-control" placeholder="Enter style name" />
                </div>
                <!-- CMS Page Selector -->
                <div class="form-group">
                    <label for="cmsSelect">Assign to CMS Page:</label>
                    <select id="cmsSelect" v-model="selectedCmsId" class="form-control">
                        <option v-for="page in cmsPages" :key="page.id" :value="page.id">{{ page.title }}</option>
                    </select>
                </div>
                <!-- IDE-like Textarea -->
                <textarea
                    ref="editorRef"
                    v-model="styleContent"
                    @keydown="handleKeydown"
                    placeholder="Enter custom CSS here"
                    style="height: 450px;">
                </textarea>
                <!-- Action Buttons -->
                <div class="mt-2">
                    <button class="btn btn-primary" @click="applyStyle">
                        Apply Style
                    </button>
                    <button class="btn btn-success ml-2" @click="saveStyleToDb">
                        Save Style
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.festival-page {
    padding: 20px;
}

/* Styles for the draggable modal */
.style-modal {
    position: absolute;
    width: 600px;
    height: 860px;
    background: #fff;
    border: 1px solid #ccc;
    box-shadow: 2px 2px 12px rgba(0, 0, 0, 0.2);
    z-index: 9999;
    padding: 0;
}

.modal-header {
    background: #f1f1f1;
    padding: 8px 10px;
    cursor: move;
    display: flex;
    justify-content: space-between;
    align-items: center;
    user-select: none;
}

.close-btn {
    background: transparent;
    border: none;
    font-weight: bold;
    cursor: pointer;
}

.modal-body {
    padding: 10px;
}

.modal-body textarea {
    width: 100%;
    padding: 5px;
    border: 1px solid #ccc;
    resize: none;
}

.form-group {
    margin-bottom: 1rem;
}

.form-control {
    width: 100%;
    padding: 0.375rem 0.75rem;
    margin-top: 0.25rem;
}
</style>
