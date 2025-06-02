<script setup lang="ts">
import AdminAppLayout from "../Layouts/AdminAppLayout.vue";
import {onBeforeUnmount, onMounted, ref} from 'vue';
import { Editor, EditorContent } from '@tiptap/vue-3';
import {refresh} from "../../../utils";
import StarterKit from '@tiptap/starter-kit';
import { Color } from '@tiptap/extension-color';
import TextStyle from '@tiptap/extension-text-style';
import ListItem from '@tiptap/extension-list-item';
import axios from "axios";
import {useForm, usePage} from "@inertiajs/vue3";

const page = usePage();
const csrfToken = page.props.csrf_token as string || "";

// Defines structure of the dashboard statistics
interface DashboardProps {
    events: {
        total: number;
        latest: Array<{ id: number; name: string; created_at: string }>;
    };
    employees: {
        total: number;
        admins: number;
        latest: Array<{ id: number; firstName: string; lastName: string; role: string; created_at: string }>;
    };
    tickets: {
        placeholder?: boolean;
        total?: number;
        revenue?: number;
        byFestival?: Array<{ id: number; name: string; count: number; type: number }>;
        recentSales?: Array<{ id: number; customer: string; quantity: number; created_at: string }>;
    };
}

// Function to get appropriate Bootstrap class for progress bars based on festival type
function getBarClass(festivalType: number): string {
    switch(festivalType) {
        case 0: return 'bg-primary'; // Jazz
        case 1: return 'bg-success'; // Food
        case 2: return 'bg-warning'; // History
        case 3: return 'bg-danger';  // Teylers
        default: return 'bg-info';
    }
}

// Destructure the 'stats' prop, and allow adding more props later
const { stats, homepageContent } = defineProps<{
    stats: DashboardProps,
    homepageContent: string | null
}>();

// Single WYSIWYG CMS box with extended toolbar
const editor = ref<Editor | null>(null);
const isSaving = ref(false);
const saveError = ref<string | null>(null);
const saveSuccess = ref(false);

// Initialize the editor when component is mounted
onMounted(() => {
    editor.value = new Editor({
        extensions: [
            StarterKit,
            Color.configure({ types: [TextStyle.name, ListItem.name] }),
            TextStyle.configure({ types: [ListItem.name] }),
        ],
        // Use homepageContent from props if available, otherwise use default content
        content: homepageContent || '<h2>Welcome to the Festival Dashboard</h2>',
    });
});

// Clean up the editor when component is unmounted
onBeforeUnmount(() => {
    if (editor.value) {
        editor.value.destroy();
    }
});

// Save editor content to backend
const saveContent = async () => {
    if (!editor.value) return;

    isSaving.value = true;
    saveError.value = null;
    saveSuccess.value = false;

    try {
        const content = editor.value.getHTML();

        // Use Inertia form for submission with CSRF protection
        const form = useForm({
            content: content,
            _token: csrfToken
        });

        await form.post('/admin/dashboard/homepage-content', {
            onSuccess: () => {
                saveSuccess.value = true;
                setTimeout(() => {
                    saveSuccess.value = false;
                }, 3000); // Hide success message after 3 seconds
            },
            onError: (errors) => {
                saveError.value = errors.content || 'Failed to save content';
            }
        });
    } catch (error: any) {
        console.error('Error saving content:', error);
        saveError.value = error.message || 'An unexpected error occurred';
    } finally {
        isSaving.value = false;
    }
};

// Function for handling file uploads (hero image)
const uploading = ref(false);
const uploadError = ref<string|null>(null);
const currentHeroUrl = ref<string>('');

// reload the existing hero.png (cache-busted)
function loadCurrentHero() {
    axios.get('/api/homepage/hero-image')
        .then(response => {
            if (response.data.path) {
                currentHeroUrl.value = `/storage/${response.data.path}?${Date.now()}`;
            } else {
                currentHeroUrl.value = '';
            }
        })
        .catch(() => {
            currentHeroUrl.value = '';
        });
}

onMounted(loadCurrentHero);

async function uploadHero(e: Event) {
    const input = e.target as HTMLInputElement;
    if (!input.files?.length) return;
    const file = input.files[0];

    if (!file.type.startsWith('image/')) {
        uploadError.value = 'Only image files are allowed.';
        return;
    }

    const formData = new FormData();
    formData.append('hero', file);
    formData.append('_token', csrfToken);

    uploading.value = true;
    uploadError.value = null;

    try {
        const response = await axios.post('/admin/dashboard/hero', formData, {
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        });

        console.log('Upload successful');
        loadCurrentHero();
    } catch (error: any) {
        console.error('Upload error:', error);

        if (error.response) {
            console.error('Response data:', error.response.data);

            // Display validation errors from Laravel
            if (error.response.status === 422 && error.response.data.errors) {
                if (error.response.data.errors.hero) {
                    uploadError.value = error.response.data.errors.hero[0];
                } else {
                    uploadError.value = 'Validation failed';
                }
            } else {
                uploadError.value = error.response.data.message || 'Upload failed';
            }
        } else {
            uploadError.value = error.message || 'Upload failed';
        }
    } finally {
        uploading.value = false;
    }
}
</script>

<template>
    <AdminAppLayout title="Dashboard">
        <div class="container-fluid p-4">
            <h1 class="mb-4">Dashboard</h1>

            <div class="row">
                <!-- Events Column -->
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-header bg-primary text-white">
                            <h5 class="card-title mb-0">Events</h5>
                        </div>
                        <div class="card-body">
                            <div class="stats-box mb-4">
                                <h3 class="text-primary">{{ stats.events.total }}</h3>
                                <p class="text-muted">Total Events</p>
                            </div>

                            <h6 class="card-subtitle mb-3">Latest Events</h6>
                            <div class="latest-items">
                                <div v-for="event in stats.events.latest" :key="event.id" class="latest-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span>{{ event.name }}</span>
                                        <small class="text-muted">{{ new Date(event.created_at).toLocaleDateString() }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Employees Column -->
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-header bg-success text-white">
                            <h5 class="card-title mb-0">Employees</h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-4">
                                <div class="col-6">
                                    <div class="stats-box">
                                        <h3 class="text-success">{{ stats.employees.total }}</h3>
                                        <p class="text-muted">Total Users</p>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="stats-box">
                                        <h3 class="text-success">{{ stats.employees.admins }}</h3>
                                        <p class="text-muted">Admins</p>
                                    </div>
                                </div>
                            </div>

                            <h6 class="card-subtitle mb-3">Latest Registrations</h6>
                            <div class="latest-items">
                                <div v-for="employee in stats.employees.latest" :key="employee.id" class="latest-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span>{{ employee.firstName }} {{ employee.lastName }}</span>
                                        <span class="badge" :class="employee.role === 'admin' ? 'bg-success' : 'bg-secondary'">{{ employee.role }}</span>
                                    </div>
                                    <small class="text-muted">{{ new Date(employee.created_at).toLocaleDateString() }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tickets Column -->
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-header bg-info text-white">
                            <h5 class="card-title mb-0">Tickets</h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-4">
                                <div class="col-6">
                                    <div class="stats-box">
                                        <h3 class="text-info">{{ stats.tickets.total || 0 }}</h3>
                                        <p class="text-muted">Total Sold</p>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="stats-box">
                                        <h3 class="text-info">€{{ stats.tickets.revenue || 0 }}</h3>
                                        <p class="text-muted">Revenue</p>
                                    </div>
                                </div>
                            </div>

                            <h6 class="card-subtitle mb-3">Tickets by Festival</h6>
                            <div class="festival-tickets-chart mb-3">
                                <div v-for="festival in stats.tickets.byFestival" :key="festival.id" 
                                     class="festival-ticket-bar">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span>{{ festival.name }}</span>
                                        <span>{{ festival.count }}</span>
                                    </div>
                                    <div class="progress">
                                        <div class="progress-bar" :style="`width: ${(festival.count / stats.tickets.total) * 100}%`" 
                                             :class="getBarClass(festival.type)"></div>
                                    </div>
                                </div>
                                <div v-if="!stats.tickets.byFestival?.length" class="text-center text-muted py-3">
                                    No tickets sold yet
                                </div>
                            </div>

                            <h6 class="card-subtitle mb-3">Recent Sales</h6>
                            <div class="latest-items">
                                <div v-for="sale in stats.tickets.recentSales" :key="sale.id" class="latest-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span>{{ sale.customer }}</span>
                                        <span class="badge bg-info">{{ sale.quantity }} tickets</span>
                                    </div>
                                    <small class="text-muted">{{ new Date(sale.created_at).toLocaleDateString() }}</small>
                                </div>
                                <div v-if="!stats.tickets.recentSales?.length" class="text-center text-muted py-3">
                                    No recent sales
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Content Editor -->
                <div class="col-md-12 mb-4">
                    <div class="card h-100">
                        <div class="card-header bg-primary text-white">
                            <h5 class="card-title mb-0">Main Page Content</h5>
                        </div>
                        <div class="m-3">
                            <label for="heroImage" class="form-label">Hero Image (PNG only)</label>
                            <input
                                id="heroImage"
                                type="file"
                                accept=".png"
                                class="form-control"
                                :disabled="uploading"
                                @change="uploadHero"
                            />
                            <div v-if="uploadError" class="text-danger mt-1">{{ uploadError }}</div>
                            <div v-if="uploading" class="text-muted mt-1">Uploading…</div>

                            <div v-if="currentHeroUrl" class="mt-3 text-center">
                                <p class="mb-1">Current Hero Preview:</p>
                                <img
                                    :src="currentHeroUrl"
                                    alt="Current Hero"
                                    class="img-thumbnail"
                                    style="max-width: 200px;"
                                />
                            </div>
                        </div>
                        <div class="control-group m-3">
                            <label>Content</label>
                            <div class="button-group">
                                <button @click="editor?.chain().focus().toggleBold().run()"
                                        :disabled="!editor?.can().chain().focus().toggleBold().run()"
                                        :class="{ 'is-active': editor?.isActive('bold') }">Bold</button>
                                <button @click="editor?.chain().focus().toggleItalic().run()"
                                        :disabled="!editor?.can().chain().focus().toggleItalic().run()"
                                        :class="{ 'is-active': editor?.isActive('italic') }">Italic</button>
                                <button @click="editor?.chain().focus().toggleStrike().run()"
                                        :disabled="!editor?.can().chain().focus().toggleStrike().run()"
                                        :class="{ 'is-active': editor?.isActive('strike') }">Strike</button>
                                <button @click="editor?.chain().focus().toggleCode().run()"
                                        :disabled="!editor?.can().chain().focus().toggleCode().run()"
                                        :class="{ 'is-active': editor?.isActive('code') }">Code</button>
                                <button @click="editor?.chain().focus().unsetAllMarks().run()">Clear marks</button>
                                <button @click="editor?.chain().focus().clearNodes().run()">Clear nodes</button>
                                <button @click="editor?.chain().focus().setParagraph().run()"
                                        :class="{ 'is-active': editor?.isActive('paragraph') }">Paragraph</button>
                                <button @click="editor?.chain().focus().toggleHeading({ level: 1 }).run()"
                                        :class="{ 'is-active': editor?.isActive('heading', { level: 1 }) }">H1</button>
                                <button @click="editor?.chain().focus().toggleHeading({ level: 2 }).run()"
                                        :class="{ 'is-active': editor?.isActive('heading', { level: 2 }) }">H2</button>
                                <button @click="editor?.chain().focus().toggleHeading({ level: 3 }).run()"
                                        :class="{ 'is-active': editor?.isActive('heading', { level: 3 }) }">H3</button>
                                <button @click="editor?.chain().focus().toggleHeading({ level: 4 }).run()"
                                        :class="{ 'is-active': editor?.isActive('heading', { level: 4 }) }">H4</button>
                                <button @click="editor?.chain().focus().toggleHeading({ level: 5 }).run()"
                                        :class="{ 'is-active': editor?.isActive('heading', { level: 5 }) }">H5</button>
                                <button @click="editor?.chain().focus().toggleHeading({ level: 6 }).run()"
                                        :class="{ 'is-active': editor?.isActive('heading', { level: 6 }) }">H6</button>
                                <button @click="editor?.chain().focus().toggleBulletList().run()"
                                        :class="{ 'is-active': editor?.isActive('bulletList') }">Bullet list</button>
                                <button @click="editor?.chain().focus().toggleOrderedList().run()"
                                        :class="{ 'is-active': editor?.isActive('orderedList') }">Ordered list</button>
                                <button @click="editor?.chain().focus().toggleCodeBlock().run()"
                                        :class="{ 'is-active': editor?.isActive('codeBlock') }">Code block</button>
                                <button @click="editor?.chain().focus().toggleBlockquote().run()"
                                        :class="{ 'is-active': editor?.isActive('blockquote') }">Blockquote</button>
                                <button @click="editor?.chain().focus().setHorizontalRule().run()">Horizontal rule</button>
                                <button @click="editor?.chain().focus().setHardBreak().run()">Hard break</button>
                                <button @click="editor?.chain().focus().undo().run()"
                                        :disabled="!editor?.can().chain().focus().undo().run()">Undo</button>
                                <button @click="editor?.chain().focus().redo().run()"
                                        :disabled="!editor?.can().chain().focus().redo().run()">Redo</button>
                                <button @click="editor?.chain().focus().setColor('#958DF1').run()"
                                        :class="{ 'is-active': editor?.isActive('textStyle', { color: '#958DF1' }) }">Purple</button>
                            </div>
                        </div>
                        <div class="w-90 m-3">
                            <EditorContent v-if="editor" :editor="editor" class="editor-content tiptap border-1 border-black"/>
                        </div>

                        <!-- Feedback messages -->
                        <div v-if="saveSuccess" class="alert alert-success mx-3" role="alert">
                            Content saved successfully!
                        </div>
                        <div v-if="saveError" class="alert alert-danger mx-3" role="alert">
                            {{ saveError }}
                        </div>

                        <div class="d-flex flex-row gap-3 w-100 m-3 ">
                            <button class="btn btn-primary" @click="saveContent" :disabled="isSaving">
                                {{ isSaving ? 'Saving...' : 'Save Content' }}
                            </button>
                            <button class="btn btn-danger" @click="refresh">
                                Cancel
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminAppLayout>
</template>


<style lang="scss" scoped>

textarea:focus, input:focus {
    outline: none;
}

.stats-box {
    text-align: center;
    padding: 1rem;
    background-color: #f8f9fa;
    border-radius: 8px;
}

.stats-box h3 {
    margin: 0;
    font-size: 2rem;
}

.latest-items {
    max-height: 300px;
    overflow-y: auto;
}

.latest-item {
    padding: 0.75rem;
    border-bottom: 1px solid #eee;
}

.latest-item:last-child {
    border-bottom: none;
}

.placeholder-content {
    padding: 2rem;
}

.card {
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
}

.card-header {
    border-bottom: none;
}

.container-fluid {
    .button-group button {
        margin: 0.2rem;
        padding: 0.5rem 0.75rem;
        border: none;
        border-radius: 4px;
        background-color: #f0f0f0;
        cursor: pointer;
    }
    .button-group button.is-active {
        background-color: #d0d0d0;
    }
}

.tiptap {

    :focus {
        outline: none;
    }

    :first-child {
        margin-top: 0;
    }

    ul, ol {
        padding: 0 1rem;
        margin: 1.25rem 1rem 1.25rem 0.4rem;
        li p {
            margin: 0.25em 0;
        }
    }
    h1, h2, h3, h4, h5, h6 {
        line-height: 1.1;
        margin-top: 2.5rem;
        text-wrap: pretty;
    }
    h1, h2 {
        margin: 3.5rem 0 1.5rem;
    }
    h1 { font-size: 1.4rem; }
    h2 { font-size: 1.2rem; }
    h3 { font-size: 1.1rem; }
    h4, h5, h6 { font-size: 1rem; }
    code {
        background-color: #f0e6ff;
        border-radius: 0.4rem;
        padding: 0.25em 0.3em;
        font-size: 0.85rem;
    }
    pre {
        background: #2d2d2d;
        border-radius: 0.5rem;
        color: #fff;
        font-family: 'JetBrains Mono', monospace;
        margin: 1.5rem 0;
        padding: 0.75rem 1rem;
        code {
            background: none;
            color: inherit;
            font-size: 0.8rem;
            padding: 0;
        }
    }
    blockquote {
        border-left: 3px solid #ccc;
        margin: 1.5rem 0;
        padding-left: 1rem;
    }
    hr {
        border: none;
        border-top: 1px solid #ddd;
        margin: 2rem 0;
    }
}

.festival-tickets-chart {
    max-height: 200px;
    overflow-y: auto;
}

.festival-ticket-bar {
    margin-bottom: 0.75rem;
}

.progress {
    height: 10px;
    border-radius: 5px;
}
</style>
