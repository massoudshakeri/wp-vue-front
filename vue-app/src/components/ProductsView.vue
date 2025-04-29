<template>
  <div>
    <h2>Products Management</h2>
    <button @click="showAddForm = true" v-if="!showAddForm">Add New Product</button>
    
    <form v-if="showAddForm" @submit.prevent="handleSubmit" class="product-form">
      <input v-model="newProduct.name" type="text" placeholder="Product Name" required>
      <input v-model="newProduct.description" type="text" placeholder="Description">
      <input v-model="newProduct.price" type="number" step="0.01" placeholder="Price" required>
      <input v-model="newProduct.stock" type="number" placeholder="Stock" required>
      <button type="submit">Save</button>
      <button type="button" @click="handleCancelClick">Cancel</button>
    </form>

    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Name</th>
          <th>Description</th>
          <th>Price</th>
          <th>Stock</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="product in products" :key="product.id">
          <td>
            <div v-show="!product.isEditing">{{ product.id }}</div>
            <div v-show="product.isEditing">{{ product.id }}</div>
          </td>
          <td>
            <div v-show="!product.isEditing">{{ product.name }}</div>
            <input v-show="product.isEditing" v-model="editingProduct.name" type="text">
          </td>
          <td>
            <div v-show="!product.isEditing">{{ product.description }}</div>
            <input v-show="product.isEditing" v-model="editingProduct.description" type="text">
          </td>
          <td>
            <div v-show="!product.isEditing">{{ product.price }}</div>
            <input v-show="product.isEditing" v-model="editingProduct.price" type="number" step="0.01">
          </td>
          <td>
            <div v-show="!product.isEditing">{{ product.stock }}</div>
            <input v-show="product.isEditing" v-model="editingProduct.stock" type="number">
          </td>
          <td>
            <template v-if="!product.isEditing">
              <button @click="startEdit(product)">Edit</button>
              <button @click="deleteProduct(product)">Delete</button>
            </template>
            <template v-else>
              <button @click="saveEdit(product)">Save</button>
              <button @click="handleCancelEditClick($event, product)">Cancel</button>
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
  products: Array<{
    id: number;
    name: string;
    description: string;
    price: number;
    stock: number;
    isEditing?: boolean;
    nonce: string;
  }>;
}>();

const showAddForm = ref(false);
const newProduct = ref({
  name: '',
  description: '',
  price: 0,
  stock: 0,
  nonce: ''
});

const editingProduct = ref({
  id: 0,
  name: '',
  description: '',
  price: 0,
  stock: 0,
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
        action: 'wp_vue_front_products',
        task: 'products_add',
        name: newProduct.value.name,
        description: newProduct.value.description,
        price: newProduct.value.price.toString(),
        stock: newProduct.value.stock.toString(),
        nonce: newProduct.value.nonce
      })
    });

    if (!response.ok) {
      throw new Error('Failed to add product');
    }

    const data = await response.json();
    console.log('Product added:', data);
    newProduct.value = {
      name: '',
      description: '',
      price: 0,
      stock: 0,
      nonce: ''
    };
    showAddForm.value = false;
  } catch (error) {
    alert('Something went wrong while adding the product');
    console.error('Error:', error);
  }
};

const startEdit = (product: typeof props.products[0]) => {
  editingProduct.value = { ...product };
  product.isEditing = true;
};

const saveEdit = async (product: typeof props.products[0]) => {
  try {
    const response = await fetch('/wp-admin/admin-ajax.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
      },
      body: new URLSearchParams({
        action: 'wp_vue_front_products',
        task: 'products_update',
        id: editingProduct.value.id.toString(),
        name: editingProduct.value.name,
        description: editingProduct.value.description,
        price: editingProduct.value.price.toString(),
        stock: editingProduct.value.stock.toString(),
        nonce: editingProduct.value.nonce
      })
    });

    if (!response.ok) {
      throw new Error('Failed to update product');
    }

    const data = await response.json();
    console.log('Product updated:', data);
    Object.assign(product, editingProduct.value);
    product.isEditing = false;
    editingProduct.value = {
      id: 0,
      name: '',
      description: '',
      price: 0,
      stock: 0,
      nonce: ''
    };
  } catch (error) {
    alert('Something went wrong while updating the product');
    console.error('Error:', error);
  }
};

const handleCancelClick = () => {
  showAddForm.value = false;
  newProduct.value = {
    name: '',
    description: '',
    price: 0,
    stock: 0,
    nonce: ''
  };
};

const handleCancelEditClick = (event: MouseEvent, product: typeof props.products[0]) => {
  product.isEditing = false;
  editingProduct.value = {
    id: 0,
    name: '',
    description: '',
    price: 0,
    stock: 0,
    nonce: ''
  };
};

const deleteProduct = async (product: typeof props.products[0]) => {
  if (confirm('Are you sure you want to delete this product?')) {
    try {
      const response = await fetch('/wp-admin/admin-ajax.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
          action: 'wp_vue_front_products',
          task: 'products_delete',
          id: product.id.toString(),
          nonce: product.nonce
        })
      });

      if (!response.ok) {
        throw new Error('Failed to delete product');
      }

      const data = await response.json();
      console.log('Product deleted:', data);
    } catch (error) {
      alert('Something went wrong while deleting the product');
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
.product-form {
  margin: 20px 0;
  padding: 15px;
  border: 1px solid #ddd;
  background-color: #f9f9f9;
}
.product-form input {
  margin-right: 10px;
  padding: 5px;
  width: 200px;
}
</style> 