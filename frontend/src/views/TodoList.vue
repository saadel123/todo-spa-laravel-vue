<script setup lang="ts">
import { ref, onMounted } from 'vue';
import api from '../api';

interface Todo {
    id: number;
    title: string;
    completed: boolean;
}

const todos = ref<Todo[]>([]);
const newTodo = ref('');
const isLoading = ref(false);
const isAdding = ref(false);
const updatingIds = ref<number[]>([]); // Track todos being updated

const fetchTodos = async () => {
    isLoading.value = true;
    try {
        const response = await api.get('/todos');
        todos.value = response.data;
    } finally {
        isLoading.value = false;
    }
};

const addTodo = async () => {
    if (!newTodo.value.trim()) return;
    isAdding.value = true;
    try {
        const response = await api.post('/todos', { title: newTodo.value });
        todos.value.unshift({ ...response.data, completed: false }); // Add new todo at top
        newTodo.value = '';
    } finally {
        isAdding.value = false;
    }
};

const toggleTodo = async (todo: Todo) => {
    updatingIds.value.push(todo.id);
    try {
        const response = await api.post(`/todos/${todo.id}`, {
            completed: !todo.completed
        });
        // Update local state
        todos.value = todos.value.map(t =>
            t.id === todo.id ? { ...t, completed: response.data.completed } : t
        );
    } finally {
        updatingIds.value = updatingIds.value.filter(id => id !== todo.id);
    }
};

onMounted(fetchTodos);
</script>

<template>
    <v-container class="max-width-md">
        <!-- Loading overlay for initial load -->
        <v-overlay :model-value="isLoading && todos.length === 0" class="align-center justify-center" persistent>
            <v-progress-circular color="primary" indeterminate size="64"></v-progress-circular>
        </v-overlay>

        <v-card class="pa-4 mb-4" elevation="4">
            <v-card-title class="text-h4 font-weight-bold text-center mb-4">
                To-Do App
            </v-card-title>

            <v-card-subtitle class="text-center mb-4">
                Organize your tasks efficiently
            </v-card-subtitle>

            <v-card-text>
                <div class="d-flex align-center">
                    <v-text-field v-model="newTodo" label="Add a new task" variant="outlined" clearable
                        @keyup.enter="addTodo" class="mr-2" :disabled="isAdding"></v-text-field>
                    <v-btn color="primary" size="large" @click="addTodo" :disabled="!newTodo.trim() || isAdding"
                        :loading="isAdding">
                        <template v-slot:prepend>
                            <v-icon>mdi-plus</v-icon>
                        </template>
                        Add
                    </v-btn>
                </div>
            </v-card-text>
        </v-card>

        <v-card elevation="4">
            <v-card-title class="text-h5 font-weight-medium">
                Your Tasks
                <v-chip class="ml-2" color="primary">
                    {{ todos.length }}
                </v-chip>
            </v-card-title>

            <v-divider></v-divider>

            <!-- Skeleton loader for initial load -->
            <template v-if="isLoading && todos.length === 0">
                <v-skeleton-loader v-for="n in 3" :key="n" type="list-item-avatar-two-line"
                    class="pa-4"></v-skeleton-loader>
            </template>

            <v-list class="py-0" v-if="todos.length > 0">
                <v-list-item v-for="todo in todos" :key="todo.id" class="px-4">
                    <template v-slot:prepend>
                        <v-checkbox :model-value="todo.completed" @click="toggleTodo(todo)" color="primary" hide-details
                            :disabled="updatingIds.includes(todo.id)"></v-checkbox>
                    </template>

                    <v-list-item-title class="text-body-1" :class="{
                        'text-decoration-line-through': todo.completed,
                        'text-grey-darken-1': todo.completed
                    }">
                        {{ todo.title }}
                        <v-chip v-if="todo.completed" size="x-small" color="success" class="ml-2">
                            Done
                        </v-chip>
                    </v-list-item-title>

                    <template v-slot:append>
                        <v-btn icon variant="text" :color="todo.completed ? 'warning' : 'success'"
                            @click="toggleTodo(todo)" :loading="updatingIds.includes(todo.id)"
                            :disabled="updatingIds.includes(todo.id)">
                            <v-icon>
                                {{ todo.completed ? 'mdi-undo' : 'mdi-check' }}
                            </v-icon>
                        </v-btn>
                    </template>
                </v-list-item>
            </v-list>

            <v-card-text v-else class="text-center text-medium-emphasis py-8">
                <v-icon size="64" color="grey-lighten-1">mdi-check-circle-outline</v-icon>
                <div class="text-h6 mt-2">No tasks yet!</div>
                <div class="text-body-1">Add your first task above</div>
            </v-card-text>
        </v-card>
    </v-container>
</template>

<style scoped>
.max-width-md {
    max-width: 800px;
}
</style>