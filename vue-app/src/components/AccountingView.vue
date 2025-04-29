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
table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 20px;
}
th, td {
  border: 1px solid #ddd;
  padding: 8px;
  text-align: left;
}
th {
  background-color: #f2f2f2;
}
button {
  margin: 0 5px;
  padding: 5px 10px;
  cursor: pointer;
}
.accounting-form {
  margin: 20px 0;
  padding: 15px;
  border: 1px solid #ddd;
  background-color: #f9f9f9;
}
.accounting-form input,
.accounting-form select {
  margin-right: 10px;
  padding: 5px;
  width: 200px;
}
</style> 