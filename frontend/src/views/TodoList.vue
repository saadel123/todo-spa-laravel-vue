<script setup lang="ts">
import { ref, onMounted } from 'vue';
import api from '../api';

// Define the structure of a Todo item
interface Todo {
    id: number;
    title: string;
    completed: boolean;
    reminder_at?: string | null;
    reminded_at?: string | null;
}

// Define toast notification states
const toastMessage = ref('');
const toastColor = ref('');
const showToast = ref(false);

// Reactive state
const todos = ref<Todo[]>([]);
const newTodo = ref('');
const reminderDate = ref<string | null>(null);
const reminderTime = ref<string | null>(null);
const isLoading = ref(false);
const isAdding = ref(false);
const updatingIds = ref<number[]>([]);
const showDatePicker = ref(false);

// Show toast notification
const showNotification = (message: string, color: string) => {
    toastMessage.value = message;
    toastColor.value = color;
    showToast.value = true;
};


// Fetch todos from the API and update state
const fetchTodos = async () => {
    isLoading.value = true;
    try {
        const response = await api.get('/todos');
        todos.value = response.data.data;
    } catch (error) {
        console.error('Error fetching todos:', error);
    } finally {
        isLoading.value = false;
    }
};

// Add a new todo to the list
const addTodo = async () => {
    const trimmedTitle = newTodo.value.trim();

    // Validation: Prevent empty title
    if (!trimmedTitle) return;

    // Validation: Prevent titles longer than 250 characters
    if (trimmedTitle.length > 250) {
        showNotification("The title cannot exceed 250 characters.", "red");
        return;
    }
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
        todos.value.unshift(response.data.data);
        newTodo.value = '';
        reminderDate.value = null;
        reminderTime.value = null;
        showNotification(response.data.message, 'green');
    } catch (error) {
        console.error('Error adding todo:', error);
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
        showNotification(response.data.message, response.data.completed ? 'green' : 'yellow');
    } catch (error) {
        console.error('Error toggling todo:', error);
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
        showNotification('Task deleted successfully!', 'red');
    } catch (error) {
        console.error('Error deleting todo:', error);
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

onMounted(fetchTodos);
</script>

<template>
    <v-container class="max-width-md">
        <!-- Main todo card with input form -->
        <v-card class="pa-4 mb-4" elevation="4">
            <!-- Card Title -->
            <v-card-title class="text-center mb-2">
                <v-icon size="40" color="primary" class="mr-2">mdi-checkbox-marked-circle-outline</v-icon>
                <span class="text-h4 font-weight-bold primary--text">To-Do App</span>
            </v-card-title>

            <!-- Subtitle -->
            <v-card-subtitle class="text-center mb-4 text-body-1 text-medium-emphasis">
                Organize your tasks efficiently with smart reminders
            </v-card-subtitle>

            <v-card-text>
                <v-container>
                    <v-row>
                        <!-- Full width input for new Todo -->
                        <v-col cols="12">
                            <v-text-field v-model="newTodo" label="What needs to be done?" variant="outlined" clearable
                                @keyup.enter="addTodo" :disabled="isAdding" prepend-inner-icon="mdi-pencil"
                                density="comfortable">
                            </v-text-field>
                        </v-col>
                    </v-row>

                    <v-row align="center">
                        <!-- Reminder Date -->
                        <v-col cols="4">
                            <v-menu v-model="showDatePicker" :close-on-content-click="false">
                                <template v-slot:activator="{ props }">
                                    <v-text-field v-bind="props"
                                        :model-value="reminderDate ? new Date(reminderDate).toLocaleDateString() : ''"
                                        label="Set Reminder Date" prepend-inner-icon="mdi-calendar" readonly
                                        variant="outlined" clearable density="comfortable"
                                        @click:clear="reminderDate = null">
                                    </v-text-field>
                                </template>
                                <v-date-picker v-model="reminderDate"
                                    :min="new Date().toISOString().split('T')[0]"></v-date-picker>
                            </v-menu>
                        </v-col>

                        <!-- Reminder Time -->
                        <v-col cols="4">
                            <v-text-field v-model="reminderTime" label="Time" type="time" variant="outlined"
                                prepend-inner-icon="mdi-clock-outline" density="comfortable" :disabled="!reminderDate">
                            </v-text-field>
                        </v-col>

                        <!-- Add Button (matching input height) -->
                        <v-col cols="4" class="d-flex align-center" style="margin-top: -21px;">
                            <v-btn color="primary" size="large" block @click="addTodo"
                                :disabled="!newTodo.trim() || isAdding" :loading="isAdding" style="height: 48px;">
                                <v-icon class="mr-1">mdi-plus</v-icon> ADD
                            </v-btn>
                        </v-col>
                    </v-row>
                </v-container>
            </v-card-text>
        </v-card>

        <!-- Todo list display -->
        <v-card elevation="4">
            <v-card-title class="px-4 py-3">
                <span class="text-h5 font-weight-medium">Your Tasks</span>
                <v-chip class="ml-2" color="primary" label>
                    <v-icon start>mdi-format-list-checks</v-icon>
                    {{ todos.length }}
                </v-chip>
            </v-card-title>

            <v-divider class="mb-2"></v-divider>

            <!-- Loading skeleton for initial load -->
            <template v-if="isLoading && todos.length === 0">
                <v-skeleton-loader v-for="n in 4" :key="n" type="list-item-avatar-two-line" class="pa-4"
                    animation="wave"></v-skeleton-loader>
            </template>

            <!-- Todo list items -->
            <v-list class="py-0" v-else-if="todos.length > 0">
                <v-slide-y-transition group>
                    <v-list-item v-for="todo in todos" :key="todo.id" class="px-4 todo-item"
                        :class="{ 'completed-item': todo.completed }">
                        <template v-slot:prepend>
                            <v-checkbox :model-value="todo.completed" @click="toggleTodo(todo)" color="primary"
                                hide-details :disabled="updatingIds.includes(todo.id)" class="mr-2"></v-checkbox>
                        </template>

                        <v-list-item-title class="text-body-1 d-flex align-start">
                            <span class="todo-title"
                                :class="{ 'text-decoration-line-through text-medium-emphasis': todo.completed }">
                                {{ todo.title }}
                            </span>
                            <div class="status-chips ml-2">
                                <v-chip v-if="todo.completed" size="x-small" color="success" class="ml-2"
                                    prepend-icon="mdi-check">
                                    Completed
                                </v-chip>
                                <v-chip v-if="todo.reminder_at" size="x-small" color="blue" class="ml-2"
                                    :class="{ 'text-strike-through': todo.completed }" prepend-icon="mdi-alarm">
                                    {{ getReminderStatus(todo) }}
                                </v-chip>
                            </div>
                        </v-list-item-title>

                        <!-- Action buttons for each todo -->
                        <template v-slot:append>
                            <div class="action-buttons">
                                <v-btn icon variant="flat" :color="todo.completed ? 'warning' : 'success'"
                                    @click="toggleTodo(todo)" :loading="updatingIds.includes(todo.id)"
                                    :disabled="updatingIds.includes(todo.id)" class="mr-2" size="small">
                                    <v-icon>
                                        {{ todo.completed ? 'mdi-undo' : 'mdi-check' }}
                                    </v-icon>
                                </v-btn>
                                <v-btn icon variant="flat" color="error" @click="deleteTodo(todo.id)"
                                    :loading="updatingIds.includes(todo.id)" :disabled="updatingIds.includes(todo.id)"
                                    size="small">
                                    <v-icon>mdi-delete</v-icon>
                                </v-btn>
                            </div>
                        </template>
                    </v-list-item>
                </v-slide-y-transition>
            </v-list>

            <!-- Empty state message -->
            <v-card-text v-else class="text-center text-medium-emphasis py-8 empty-state">
                <v-icon size="64" color="grey-lighten-1" class="empty-icon">mdi-check-circle-outline</v-icon>
                <div class="text-h6 mt-2">No tasks found!</div>
                <div class="text-body-1">Start by adding your first task above</div>
            </v-card-text>
        </v-card>

        <!-- Toaster Notification -->
        <v-snackbar v-model="showToast" location="top right" :color="toastColor" timeout="3000">
            {{ toastMessage }}
            <template v-slot:actions>
                <v-btn text @click="showToast = false">Close</v-btn>
            </template>
        </v-snackbar>

    </v-container>
</template>

<style scoped>
.todo-title {
    max-width: 80%;
    word-wrap: break-word;
    white-space: normal;
    display: inline-block;
    overflow-wrap: break-word;
}

.status-chips {
    flex-shrink: 0;
    display: flex;
    align-items: center;
}

.action-buttons {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    min-width: 100px;
    gap: 8px;
}

.v-chip {
margin-right: 4px;
font-size: 0.75rem;
}


.max-width-md {
max-width: 800px;
}

.todo-item {
transition: all 0.3s ease;
border-radius: 8px;
margin: 4px 0;
}

.todo-item:hover {
background: rgba(var(--v-theme-primary), 0.05);
transform: translateX(4px);
}

.completed-item {
background: rgba(var(--v-theme-success), 0.05);
}

.status-chips {
display: flex;
gap: 8px;
}


.empty-state {
animation: fadeIn 0.5s ease;
}

.empty-icon {
animation: pulse 2s infinite;
}

@keyframes pulse {
0% {
transform: scale(1);
}

50% {
transform: scale(1.1);
}

100% {
transform: scale(1);
}
}

@keyframes fadeIn {
from {
opacity: 0;
}

to {
opacity: 1;
}
}
</style>