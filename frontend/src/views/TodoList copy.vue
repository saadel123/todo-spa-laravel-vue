<script setup lang="ts">
import { ref, onMounted } from 'vue';
import api from '../api';

// Define the structure of a Todo item
interface Todo {
    id: number;
    title: string;
    completed: boolean;
    reminder_at?: string | null;  // Optional reminder timestamp
    reminded_at?: string | null;  // Timestamp when reminder was sent
}

// Reactive state for our todo application
const todos = ref<Todo[]>([]);  // List of todos
const newTodo = ref('');  // Current new todo input
const reminderDate = ref<string | null>(null);  // Selected reminder date
const reminderTime = ref<string | null>(null);  // Selected reminder time
const isLoading = ref(false);  // Loading state for initial fetch
const isAdding = ref(false);  // Loading state for adding todos
const updatingIds = ref<number[]>([]);  // Track which todos are being updated
const showDatePicker = ref(false);  // Control date picker visibility

// Fetch todos from the API and update state
const fetchTodos = async () => {
    isLoading.value = true;
    try {
        const response = await api.get('/todos');
        todos.value = response.data;
    } finally {
        isLoading.value = false;
    }
};

// Add a new todo to the list
const addTodo = async () => {
    if (!newTodo.value.trim()) return;  // Don't add empty todos

    isAdding.value = true;
    try {
        // Combine date and time into a single ISO string if both are provided
        let reminderAt = null;
        if (reminderDate.value && reminderTime.value) {
            const date = new Date(reminderDate.value);
            const [hours, minutes] = reminderTime.value.split(':');
            date.setHours(parseInt(hours, 10));
            date.setMinutes(parseInt(minutes, 10));
            reminderAt = date.toISOString();
        }

        // Send new todo to server and add to local state
        const response = await api.post('/todos', {
            title: newTodo.value,
            reminder_at: reminderAt
        });

        // Add new todo to beginning of list and reset form
        todos.value.unshift(response.data);
        newTodo.value = '';
        reminderDate.value = null;
        reminderTime.value = null;
    } finally {
        isAdding.value = false;
    }
};

// Toggle todo completion status
const toggleTodo = async (todo: Todo) => {
    updatingIds.value.push(todo.id);  // Track this todo as being updated
    try {
        const response = await api.post(`/todos/${todo.id}`, {
            completed: !todo.completed
        });
        // Update local state with server response
        todos.value = todos.value.map(t =>
            t.id === todo.id ? { ...t, completed: response.data.completed } : t
        );
    } finally {
        // Remove this todo from updating state
        updatingIds.value = updatingIds.value.filter(id => id !== todo.id);
    }
};

// Delete a todo from the list
const deleteTodo = async (todoId: number) => {
    updatingIds.value.push(todoId);  // Track this todo as being updated
    try {
        await api.delete(`/todos/${todoId}`);
        // Remove todo from local state
        todos.value = todos.value.filter(t => t.id !== todoId);
    } finally {
        // Remove this todo from updating state
        updatingIds.value = updatingIds.value.filter(id => id !== todoId);
    }
};

// Format reminder date for display
const formatReminder = (dateString: string | null | undefined) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleString();  // Convert to local date/time format
};

// Get status text for a todo's reminder
const getReminderStatus = (todo: Todo) => {
    if (!todo.reminder_at) return '';  // No reminder set
    if (todo.reminded_at) return 'Reminder sent: ' + formatReminder(todo.reminded_at);  // Reminder already sent
    return 'Due: ' + formatReminder(todo.reminder_at);  // Pending reminder
};

// Fetch todos when component mounts
onMounted(fetchTodos);
</script>

<template>
    <v-container class="max-width-md">
        <!-- Loading overlay for initial data fetch -->
        <v-overlay :model-value="isLoading && todos.length === 0" class="align-center justify-center" persistent>
            <v-progress-circular color="primary" indeterminate size="64"></v-progress-circular>
        </v-overlay>

        <!-- Main todo card with input form -->
        <v-card class="pa-4 mb-4" elevation="4">
            <v-card-title class="text-h4 font-weight-bold text-center mb-4">
                To-Do App
            </v-card-title>

            <v-card-subtitle class="text-center mb-4">
                Organize your tasks efficiently
            </v-card-subtitle>

            <v-card-text>
                <!-- Todo input form -->
                <div class="d-flex align-center mb-4">
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

                <!-- Reminder date/time picker -->
                <div class="d-flex align-center">
                    <v-menu v-model="showDatePicker" :close-on-content-click="false">
                        <template v-slot:activator="{ props }">
                            <v-text-field v-bind="props"
                                :model-value="reminderDate ? new Date(reminderDate).toLocaleDateString() : ''"
                                label="Set Reminder Date" prepend-inner-icon="mdi-calendar" readonly variant="outlined"
                                class="mr-2" clearable @click:clear="reminderDate = null"></v-text-field>
                        </template>
                        <v-date-picker v-model="reminderDate"
                            :min="new Date().toISOString().split('T')[0]"></v-date-picker>
                    </v-menu>

                    <v-text-field v-model="reminderTime" label="Time" type="time" variant="outlined"
                        :disabled="!reminderDate"></v-text-field>
                </div>
            </v-card-text>
        </v-card>

        <!-- Todo list display -->
        <v-card elevation="4">
            <v-card-title class="text-h5 font-weight-medium">
                Your Tasks
                <v-chip class="ml-2" color="primary">
                    {{ todos.length }}
                </v-chip>
            </v-card-title>

            <v-divider></v-divider>

            <!-- Loading skeleton for initial load -->
            <template v-if="isLoading && todos.length === 0">
                <v-skeleton-loader v-for="n in 3" :key="n" type="list-item-avatar-two-line"
                    class="pa-4"></v-skeleton-loader>
            </template>

            <!-- Todo list items -->
            <v-list class="py-0" v-else-if="todos.length > 0">
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
                        <!-- Status chips for completion and reminders -->
                        <v-chip v-if="todo.completed" size="x-small" color="success" class="ml-2">
                            Done
                        </v-chip>
                        <v-chip v-if="todo.reminder_at" size="x-small" color="blue" class="ml-2"
                            :class="{ 'text-strike-through': todo.completed }">
                            <v-icon start size="small">mdi-alarm</v-icon>
                            {{ getReminderStatus(todo) }}
                        </v-chip>
                    </v-list-item-title>

                    <!-- Action buttons for each todo -->
                    <template v-slot:append>
                        <v-btn icon variant="text" :color="todo.completed ? 'warning' : 'success'"
                            @click="toggleTodo(todo)" :loading="updatingIds.includes(todo.id)"
                            :disabled="updatingIds.includes(todo.id)" class="mr-2">
                            <v-icon>
                                {{ todo.completed ? 'mdi-undo' : 'mdi-check' }}
                            </v-icon>
                        </v-btn>
                        <v-btn icon variant="text" color="error" @click="deleteTodo(todo.id)"
                            :loading="updatingIds.includes(todo.id)" :disabled="updatingIds.includes(todo.id)">
                            <v-icon>mdi-delete</v-icon>
                        </v-btn>
                    </template>
                </v-list-item>
            </v-list>

            <!-- Empty state message -->
            <v-card-text v-else class="text-center text-medium-emphasis py-8">
                <v-icon size="64" color="grey-lighten-1">mdi-check-circle-outline</v-icon>
                <div class="text-h6 mt-2">No tasks yet!</div>
                <div class="text-body-1">Add your first task above</div>
            </v-card-text>
        </v-card>
    </v-container>
</template>

<style scoped>
/* Responsive container */
.max-width-md {
    max-width: 800px;
}

/* Style for completed reminder chips */
.text-strike-through {
    text-decoration: line-through;
}

/* Spacing for chips and buttons */
.v-chip {
    margin-right: 4px;
}

.v-btn {
    margin-left: 4px;
}
</style>