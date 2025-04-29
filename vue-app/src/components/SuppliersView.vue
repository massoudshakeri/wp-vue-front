<template>
  <div>
    <h2>Suppliers Management</h2>
    <button @click="showAddForm = true" v-if="!showAddForm">Add New Supplier</button>
    
    <form v-if="showAddForm" @submit.prevent="handleSubmit" class="supplier-form">
      <input v-model="newSupplier.name" type="text" placeholder="Supplier Name" required>
      <input v-model="newSupplier.contact" type="text" placeholder="Contact Person">
      <input v-model="newSupplier.email" type="email" placeholder="Email">
      <input v-model="newSupplier.phone" type="tel" placeholder="Phone">
      <input v-model="newSupplier.address" type="text" placeholder="Address">
      <button type="submit">Save</button>
      <button type="button" @click="handleCancelClick">Cancel</button>
    </form>

    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Name</th>
          <th>Contact</th>
          <th>Email</th>
          <th>Phone</th>
          <th>Address</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="supplier in suppliers" :key="supplier.id">
          <td>
            <div v-show="!supplier.isEditing">{{ supplier.id }}</div>
            <div v-show="supplier.isEditing">{{ supplier.id }}</div>
          </td>
          <td>
            <div v-show="!supplier.isEditing">{{ supplier.name }}</div>
            <input v-show="supplier.isEditing" v-model="editingSupplier.name" type="text">
          </td>
          <td>
            <div v-show="!supplier.isEditing">{{ supplier.contact }}</div>
            <input v-show="supplier.isEditing" v-model="editingSupplier.contact" type="text">
          </td>
          <td>
            <div v-show="!supplier.isEditing">{{ supplier.email }}</div>
            <input v-show="supplier.isEditing" v-model="editingSupplier.email" type="email">
          </td>
          <td>
            <div v-show="!supplier.isEditing">{{ supplier.phone }}</div>
            <input v-show="supplier.isEditing" v-model="editingSupplier.phone" type="tel">
          </td>
          <td>
            <div v-show="!supplier.isEditing">{{ supplier.address }}</div>
            <input v-show="supplier.isEditing" v-model="editingSupplier.address" type="text">
          </td>
          <td>
            <template v-if="!supplier.isEditing">
              <button @click="startEdit(supplier)">Edit</button>
              <button @click="deleteSupplier(supplier)">Delete</button>
              <span v-if="supplier.showSaved" class="saved-indicator">Saved!</span>
            </template>
            <template v-else>
              <button @click="saveEdit(supplier)">Save</button>
              <button @click="handleCancelEditClick($event, supplier)">Cancel</button>
            </template>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';

const props = defineProps<{
  suppliers: Array<{
    id: number;
    name: string;
    contact: string;
    email: string;
    phone: string;
    address: string;
    isEditing?: boolean;
    showSaved?: boolean;
    nonce: string;
  }>;
}>();

const showAddForm = ref(false);
const newSupplier = ref({
  name: '',
  contact: '',
  email: '',
  phone: '',
  address: '',
  nonce: ''
});

const editingSupplier = ref({
  id: 0,
  name: '',
  contact: '',
  email: '',
  phone: '',
  address: '',
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
        action: 'wp_vue_front_suppliers',
        task: 'suppliers_add',
        name: newSupplier.value.name,
        contact: newSupplier.value.contact,
        email: newSupplier.value.email,
        phone: newSupplier.value.phone,
        address: newSupplier.value.address,
        nonce: newSupplier.value.nonce
      })
    });

    if (!response.ok) {
      throw new Error('Failed to add supplier');
    }

    const data = await response.json();
    console.log('Supplier added:', data);
    newSupplier.value = {
      name: '',
      contact: '',
      email: '',
      phone: '',
      address: '',
      nonce: ''
    };
    showAddForm.value = false;
  } catch (error) {
    alert('Something went wrong while adding the supplier');
    console.error('Error:', error);
  }
};

const startEdit = (supplier: typeof props.suppliers[0]) => {
  editingSupplier.value = { ...supplier };
  supplier.isEditing = true;
};

const saveEdit = async (supplier: typeof props.suppliers[0]) => {
  try {
    const response = await fetch('/wp-admin/admin-ajax.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
      },
      body: new URLSearchParams({
        action: 'wp_vue_front_suppliers',
        task: 'suppliers_update',
        id: editingSupplier.value.id.toString(),
        name: editingSupplier.value.name,
        contact: editingSupplier.value.contact,
        email: editingSupplier.value.email,
        phone: editingSupplier.value.phone,
        address: editingSupplier.value.address,
        nonce: editingSupplier.value.nonce
      })
    });

    if (!response.ok) {
      throw new Error('Failed to update supplier');
    }

    const data = await response.json();
    console.log('Supplier updated:', data);
    Object.assign(supplier, editingSupplier.value);
    supplier.isEditing = false;
    supplier.showSaved = true;
    setTimeout(() => {
      supplier.showSaved = false;
    }, 1000);
    editingSupplier.value = {
      id: 0,
      name: '',
      contact: '',
      email: '',
      phone: '',
      address: '',
      nonce: ''
    };
  } catch (error) {
    alert('Something went wrong while updating the supplier');
    console.error('Error:', error);
  }
};

const handleCancelClick = () => {
  showAddForm.value = false;
  newSupplier.value = { 
    name: '', 
    contact: '', 
    email: '', 
    phone: '', 
    address: '',
    nonce: ''
  };
};

const handleCancelEditClick = (event: MouseEvent, supplier: typeof props.suppliers[0]) => {
  supplier.isEditing = false;
  editingSupplier.value = {
    id: 0,
    name: '',
    contact: '',
    email: '',
    phone: '',
    address: '',
    nonce: ''
  };
};

const deleteSupplier = async (supplier: typeof props.suppliers[0]) => {
  if (confirm('Are you sure you want to delete this supplier?')) {
    try {
      const response = await fetch('/wp-admin/admin-ajax.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
          action: 'wp_vue_front_suppliers',
          task: 'suppliers_delete',
          id: supplier.id.toString(),
          nonce: supplier.nonce
        })
      });

      if (!response.ok) {
        throw new Error('Failed to delete supplier');
      }

      const data = await response.json();
      console.log('Supplier deleted:', data);
    } catch (error) {
      alert('Something went wrong while deleting the supplier');
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
  max-width: 1100px;
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
  background: #27ae60;
  color: #fff;
  font-weight: 500;
  cursor: pointer;
  transition: background 0.2s, box-shadow 0.2s;
  box-shadow: 0 1px 2px rgba(39,174,96,0.08);
}

button[type="button"] {
  background: #e67e22;
}

button:hover {
  background: #219150;
}

button[type="button"]:hover {
  background: #ca6f1e;
}

.supplier-form {
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

.supplier-form input {
  padding: 7px 10px;
  border: 1px solid #d1d5db;
  border-radius: 5px;
  background: #fff;
  font-size: 15px;
  width: 180px;
  transition: border 0.2s;
}

.supplier-form input:focus {
  border: 1.5px solid #27ae60;
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