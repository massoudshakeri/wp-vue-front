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
.supplier-form {
  margin: 20px 0;
  padding: 15px;
  border: 1px solid #ddd;
  background-color: #f9f9f9;
}
.supplier-form input {
  margin-right: 10px;
  padding: 5px;
  width: 200px;
}
</style> 