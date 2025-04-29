<template>
  <div>
    <h2>Accounting Management</h2>
    <button @click="showAddForm = true" v-if="!showAddForm">Add New Entry</button>
    
    <form v-if="showAddForm" @submit.prevent="handleSubmit" class="accounting-form">
      <input v-model="newEntry.date" type="date" required>
      <input v-model="newEntry.description" type="text" placeholder="Description" required>
      <input v-model="newEntry.amount" type="number" placeholder="Amount" required>
      <select v-model="newEntry.type" required>
        <option value="income">Income</option>
        <option value="expense">Expense</option>
      </select>
      <button type="submit">Save</button>
      <button type="button" @click="handleCancelClick">Cancel</button>
    </form>

    <table>
      <thead>
        <tr>
          <th>Date</th>
          <th>Description</th>
          <th>Amount</th>
          <th>Type</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="entry in entries" :key="entry.id">
          <td>
            <div v-show="!entry.isEditing">{{ entry.date }}</div>
            <input v-show="entry.isEditing" v-model="editingEntry.date" type="date">
          </td>
          <td>
            <div v-show="!entry.isEditing">{{ entry.description }}</div>
            <input v-show="entry.isEditing" v-model="editingEntry.description" type="text">
          </td>
          <td>
            <div v-show="!entry.isEditing">{{ entry.amount }}</div>
            <input v-show="entry.isEditing" v-model="editingEntry.amount" type="number">
          </td>
          <td>
            <div v-show="!entry.isEditing">{{ entry.type }}</div>
            <select v-show="entry.isEditing" v-model="editingEntry.type">
              <option value="income">Income</option>
              <option value="expense">Expense</option>
            </select>
          </td>
          <td>
            <template v-if="!entry.isEditing">
              <button @click="startEdit(entry)">Edit</button>
              <button @click="deleteEntry(entry)">Delete</button>
              <span v-if="entry.showSaved" class="saved-indicator">Saved!</span>
            </template>
            <template v-else>
              <button @click="saveEdit(entry)">Save</button>
              <button @click="handleCancelEditClick(entry)">Cancel</button>
            </template>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup lang="ts">
import { ref, defineProps } from 'vue';

const props = defineProps<{
  entries: Array<{
    id: number;
    date: string;
    description: string;
    amount: number;
    type: 'income' | 'expense';
    isEditing?: boolean;
    nonce: string;
    showSaved?: boolean;
  }>;
}>();

const editingEntry = ref({
  id: 0,
  date: '',
  description: '',
  amount: 0,
  type: 'income' as 'income' | 'expense',
  nonce: ''
});

const showAddForm = ref(false);
const newEntry = ref({
  date: '',
  description: '',
  amount: 0,
  type: 'income' as 'income' | 'expense',
  nonce: ''
});

const handleSubmit = async () => {
  try {
    const response = await fetch('/wp-admin/admin-ajax.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
      },
      body: new URLSearchParams({
        action: 'wp_vue_front_accounting',
        task: 'accounting_add',
        date: newEntry.value.date,
        description: newEntry.value.description,
        amount: newEntry.value.amount.toString(),
        type: newEntry.value.type,
        nonce: newEntry.value.nonce
      })
    });

    if (!response.ok) {
      throw new Error('Failed to add entry');
    }

    const data = await response.json();
    console.log('Entry added:', data);
    newEntry.value = {
      date: '',
      description: '',
      amount: 0,
      type: 'income',
      nonce: ''
    };
    showAddForm.value = false;
  } catch (error) {
    alert('Something went wrong while adding the entry');
    console.error('Error:', error);
  }
};

const startEdit = (entry: typeof props.entries[0]) => {
  editingEntry.value = {...entry};
  entry.isEditing = true;
};

const saveEdit = async (entry: typeof props.entries[0]) => {
  try {
    const response = await fetch('/wp-admin/admin-ajax.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
      },
      body: new URLSearchParams({
        action: 'wp_vue_front_accounting',
        task: 'accounting_update',
        id: editingEntry.value.id.toString(),
        date: editingEntry.value.date,
        description: editingEntry.value.description,
        amount: editingEntry.value.amount.toString(),
        type: editingEntry.value.type,
        nonce: editingEntry.value.nonce
      })
    });

    if (!response.ok) {
      throw new Error('Failed to update entry');
    }

    const data = await response.json();
    console.log('Entry updated:', data);
    Object.assign(entry, editingEntry.value);
    entry.isEditing = false;
    entry.showSaved = true;
    setTimeout(() => {
      entry.showSaved = false;
    }, 1000);
    editingEntry.value = {
      id: 0,
      date: '',
      description: '',
      amount: 0,
      type: 'income' as 'income' | 'expense',
      nonce: ''
    };
  } catch (error) {
    alert('Something went wrong while updating the entry');
    console.error('Error:', error);
  }
};

const handleCancelClick = () => {
  showAddForm.value = false;
  newEntry.value = {
    date: '',
    description: '',
    amount: 0,
    type: 'income',
    nonce: ''
  };
};

const handleCancelEditClick = (entry: typeof props.entries[0]) => {
  entry.isEditing = false;
  editingEntry.value = {
    id: 0,
    date: '',
    description: '',
    amount: 0,
    type: 'income' as 'income' | 'expense',
    nonce: ''
  };
};

const deleteEntry = async (entry: typeof props.entries[0]) => {
  if (confirm('Are you sure you want to delete this entry?')) {
    try {
      const response = await fetch('/wp-admin/admin-ajax.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
          action: 'wp_vue_front_accounting',
          task: 'accounting_delete',
          id: entry.id.toString()
        })
      });

      if (!response.ok) {
        throw new Error('Failed to delete entry');
      }

      const data = await response.json();
      console.log('Entry deleted:', data);
    } catch (error) {
      alert('Something went wrong while deleting the entry');
      console.error('Error:', error);
    }
  }
};
</script>

<style scoped>
/* Card container */
div {
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 2px 12px rgba(0,0,0,0.07);
  padding: 32px 24px;
  max-width: 900px;
  margin: 40px auto;
}

h2 {
  margin-bottom: 24px;
  color: #2c3e50;
  font-weight: 700;
  letter-spacing: 1px;
}

table {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0;
  margin-top: 24px;
  background: #fafbfc;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 1px 4px rgba(0,0,0,0.04);
}

th, td {
  padding: 14px 12px;
  text-align: left;
}

th {
  background: #f4f6f8;
  color: #34495e;
  font-weight: 600;
  border-bottom: 2px solid #e1e4e8;
}

tbody tr:nth-child(even) {
  background: #f9fafb;
}

tbody tr:hover {
  background: #eaf6fb;
  transition: background 0.2s;
}

button {
  margin: 0 4px;
  padding: 6px 16px;
  border: none;
  border-radius: 6px;
  background: #3498db;
  color: #fff;
  font-weight: 500;
  cursor: pointer;
  transition: background 0.2s, box-shadow 0.2s;
  box-shadow: 0 1px 2px rgba(52,152,219,0.08);
}

button[type="button"] {
  background: #e74c3c;
}

button:hover {
  background: #217dbb;
}

button[type="button"]:hover {
  background: #c0392b;
}

.accounting-form {
  margin: 24px 0;
  padding: 20px 18px;
  border: 1px solid #e1e4e8;
  background: #f8fafc;
  border-radius: 8px;
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
  align-items: center;
}

.accounting-form input,
.accounting-form select {
  padding: 7px 10px;
  border: 1px solid #d1d5db;
  border-radius: 5px;
  background: #fff;
  font-size: 15px;
  width: 180px;
  transition: border 0.2s;
}

.accounting-form input:focus,
.accounting-form select:focus {
  border: 1.5px solid #3498db;
  outline: none;
}

.saved-indicator {
  color: #27ae60;
  font-size: 14px;
  margin-left: 8px;
  font-weight: 500;
  opacity: 0;
  animation: fadeInOut 1s ease-in-out;
}

@keyframes fadeInOut {
  0% { opacity: 0; }
  20% { opacity: 1; }
  80% { opacity: 1; }
  100% { opacity: 0; }
}
</style>