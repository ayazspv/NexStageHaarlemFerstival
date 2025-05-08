<script setup lang="ts">
import { ref, reactive } from 'vue';
import { Inertia } from '@inertiajs/inertia';
import AdminAppLayout from '@/Pages/Layouts/AdminAppLayout.vue';
import { usePage } from '@inertiajs/vue3';
import { Game } from '../../../../models';
import axios from "axios";

const page = usePage();
const csrfToken = (page.props.csrf_token as string) || '';

const props = defineProps<{
    festival: any;
    games: Game[];
}>();

const newGame = reactive<Game>({
    title: '',
    question: '',
    option1: '',
    option2: '',
    option3: '',
    option4: '',
    correct_option: null,
    hint: '',
    thumbnail: null,
    stamp: null,
});

const gamesList = ref<Game[]>([...props.games]);

const editingGameId = ref<number | null>(null);
const editGame = reactive<Game>({
    title: '',
    question: '',
    option1: '',
    option2: '',
    option3: '',
    option4: '',
    hint: '',
    correct_option: null,
    thumbnail: null,
    stamp: null,
});

const addGame = () => {
    const formData = new FormData();
    formData.append('title', newGame.title);
    formData.append('question', newGame.question);
    formData.append('option1', newGame.option1);
    formData.append('option2', newGame.option2);
    formData.append('option3', newGame.option3);
    formData.append('option4', newGame.option4);
    formData.append('correct_option', String(newGame.correct_option));
    formData.append('hint', newGame.hint);

    if (newGame.thumbnail) {
        formData.append('thumbnail', newGame.thumbnail as Blob);
    }
    if (newGame.stamp) {
        formData.append('stamp', newGame.stamp as Blob);
    }

    Inertia.post(`/admin/festivals/${props.festival.id}/game`, formData, {
        headers: { 'X-CSRF-TOKEN': csrfToken },
        onSuccess: () => {
            newGame.title = '';
            newGame.question = '';
            newGame.option1 = '';
            newGame.option2 = '';
            newGame.option3 = '';
            newGame.option4 = '';
            newGame.correct_option = null;
            newGame.hint = '';
            newGame.thumbnail = null;
            newGame.stamp = null;
        },
    });
};


const enableEdit = (game: Game) => {
    editingGameId.value = game.id || null;
    editGame.title = game.title;
    editGame.question = game.question;
    editGame.option1 = game.option1;
    editGame.option2 = game.option2;
    editGame.option3 = game.option3;
    editGame.option4 = game.option4;
    editGame.correct_option = game.correct_option;
    editGame.hint = game.hint;
    editGame.thumbnail = null;
    editGame.stamp = null;
};

const updateGame = () => {
    if (!editingGameId.value) return;
    const formData = new FormData();
    formData.append('_method', 'PUT');
    formData.append('title', editGame.title)
    formData.append('question', editGame.question);
    formData.append('option1', editGame.option1);
    formData.append('option2', editGame.option2);
    formData.append('option3', editGame.option3);
    formData.append('option4', editGame.option4);
    formData.append('hint', editGame.hint);
    formData.append('correct_option', String(editGame.correct_option));

    if (editGame.thumbnail instanceof File) {
        formData.append('thumbnail', editGame.thumbnail);
    }
    if (editGame.stamp instanceof File) {
        formData.append('stamp', editGame.stamp);
    }

    Inertia.post(`/admin/festivals/${props.festival.id}/game/${editingGameId.value}`, formData, {
        headers: { 'X-CSRF-TOKEN': csrfToken },
        onSuccess: () => {
            editingGameId.value = null;
        },
    });
};

const deleteGame = (gameId: number) => {
    axios.delete(`/admin/festivals/${gameId}/game`, {
        headers: {
            'X-CSRF-TOKEN': csrfToken,
        },
    });
    window.location.reload();
}
</script>

<template>
    <AdminAppLayout :title="`Manage Game: ${festival.name}`">
        <div class="cms-container">
            <!-- New Game Section -->
            <section class="d-flex flex-column gap-2 new-game max-w-md mx-auto p-4 section-box mt-5 w-25">
                <h2>Add New Game</h2>
                <div class="d-flex flex-column">
                    <label>Title:</label>
                    <input v-model="newGame.title" class="form-control" placeholder="Enter title" />
                </div>
                <div class="d-flex flex-column">
                    <label>Question:</label>
                    <input v-model="newGame.question" class="form-control" placeholder="Enter question" />
                </div>
                <div class="d-flex flex-column">
                    <label>Option 1:</label>
                    <input v-model="newGame.option1" class="form-control"  placeholder="Option 1" />
                </div>
                <div class="d-flex flex-column">
                    <label>Option 2:</label>
                    <input v-model="newGame.option2" class="form-control"  placeholder="Option 2" />
                </div>
                <div class="d-flex flex-column">
                    <label>Option 3:</label>
                    <input v-model="newGame.option3" class="form-control"  placeholder="Option 3" />
                </div>
                <div class="d-flex flex-column">
                    <label>Option 4:</label>
                    <input v-model="newGame.option4" class="form-control"  placeholder="Option 4" />
                </div>
                <div class="d-flex flex-column gap-1">
                    <label>Correct Option:</label>
                    <select v-model="newGame.correct_option" class="form-select">
                        <option :value="1">Option 1</option>
                        <option :value="2">Option 2</option>
                        <option :value="3">Option 3</option>
                        <option :value="4">Option 4</option>
                    </select>
                </div>
                <div class="d-flex flex-column">
                    <label>Hint:</label>
                    <input v-model="newGame.hint" class="form-control"  placeholder="Enter hint" />
                </div>
                <div>
                    <label>Thumbnail Image:</label>
                    <input type="file" class="form-control" @change="e => newGame.thumbnail = e.target.files[0]" />
                </div>
                <div>
                    <label>Prize Stamp Image:</label>
                    <input type="file" class="form-control" @change="e => newGame.stamp = e.target.files[0]" />
                </div>
                <button @click="addGame" class="btn btn-primary mt-3">Add Game</button>
            </section>

            <!-- Existing Games Section -->
            <section class="existing-games">
                <h2>Existing Games</h2>
                <!-- Container for game boxes -->
                <div class="game-boxes">
                    <div v-for="game in gamesList" :key="game.id">
                        <!-- Edit mode for the selected game -->
                        <div v-if="editingGameId === game.id" class="d-flex flex-column gap-1 game-box section-box">
                            <h2>Edit Game</h2>
                            <div class="d-flex flex-column">
                                <label>Question:</label>
                                <input v-model="editGame.question" class="form-control" placeholder="Enter question" />
                            </div>
                            <div class="d-flex flex-column">
                                <label>Option 1:</label>
                                <input v-model="editGame.option1" class="form-control" placeholder="Option 1" />
                            </div>
                            <div class="d-flex flex-column">
                                <label>Option 2:</label>
                                <input v-model="editGame.option2" class="form-control" placeholder="Option 2" />
                            </div>
                            <div class="d-flex flex-column">
                                <label>Option 3:</label>
                                <input v-model="editGame.option3" class="form-control" placeholder="Option 3" />
                            </div>
                            <div class="d-flex flex-column">
                                <label>Option 4:</label>
                                <input v-model="editGame.option4" class="form-control" placeholder="Option 4" />
                            </div>
                            <div class="d-flex flex-column">
                                <label>Correct Option:</label>
                                <select v-model="editGame.correct_option" class="form-control">
                                    <option :value="1">Option 1</option>
                                    <option :value="2">Option 2</option>
                                    <option :value="3">Option 3</option>
                                    <option :value="4">Option 4</option>
                                </select>
                            </div>
                            <div class="d-flex flex-column">
                                <label>Thumbnail Image:</label>
                                <input type="file" class="form-control" @change="e => editGame.thumbnail = e.target.files[0]" />
                                <div v-if="game.thumbnail">
                                    <img :src="`/storage/${game.thumbnail}`" alt="Current Thumbnail" width="150px" height="150px" />
                                </div>
                            </div>
                            <div class="d-flex flex-column">
                                <label>Prize Stamp Image:</label>
                                <input type="file" class="form-control" @change="e => editGame.stamp = e.target.files[0]" />
                                <div v-if="game.stamp">
                                    <img :src="`/storage/${game.stamp}`" alt="Current Stamp" width="150px" height="150px" />
                                </div>
                            </div>
                            <button @click="updateGame" class="btn btn-primary">Save</button>
                            <button @click="editingGameId = null">Cancel</button>
                            <button @click="deleteGame(game.id)" class="btn btn-danger">Delete</button>
                        </div>
                        <!-- Display mode for a game -->
                        <div v-else class="d-flex flex-column game-box section-box">
                            <div class="d-flex flex-column">
                                <label>Question:</label>
                                <h2>{{ game.question }}</h2>
                            </div>
                            <ul class="list-group">
                                <li class="list-group-item">
                                    <label>Question 1:</label>
                                    {{ game.option1 }}
                                </li>
                                <li class="list-group-item">
                                    <label>Question 2:</label>
                                    {{ game.option2 }}
                                </li>
                                <li class="list-group-item">
                                    <label>Question 3:</label>
                                    {{ game.option3 }}
                                </li>
                                <li class="list-group-item">
                                    <label>Question 4:</label>
                                    {{ game.option4 }}
                                </li>
                            </ul>
                            <b>Correct Answer: {{ game.correct_option }}</b>
                            <div v-if="game.thumbnail">
                                <label>Thumbnail:</label>
                                <img :src="`/storage/${game.thumbnail}`" alt="Question thumbnail" width="150px" height="150px" />
                            </div>
                            <div v-if="game.stamp">
                                <label>Stamp:</label>
                                <img :src="`/storage/${game.stamp}`" alt="Prize stamp" width="150px" height="150px"/>
                            </div>
                            <button @click="enableEdit(game)" class="btn btn-primary">Edit</button>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </AdminAppLayout>
</template>

<style scoped>
.cms-container {
    padding: 1rem;
}

/* New game section retains its styling */
.new-game {
    margin-bottom: 2rem;
}

/* Game box styling */
.section-box {
    padding: 20px;
    box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
    border-radius: 8px;
    background-color: #ffffff;
}

/* Flex container for game boxes */
.game-boxes {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    justify-content: center;
}

/* Each game box gets a fixed width that can adjust as needed */
.game-box {
    width: 400px;
}
</style>
