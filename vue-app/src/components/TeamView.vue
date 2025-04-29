<template>
  <div>
    <h2>Team Management</h2>
    <button @click="showAddForm = true" v-if="!showAddForm">Add New Member</button>
    
    <form v-if="showAddForm" @submit.prevent="handleSubmit" class="team-form">
      <input v-model="newMember.name" type="text" placeholder="Name" required>
      <input v-model="newMember.position" type="text" placeholder="Position" required>
      <input v-model="newMember.email" type="email" placeholder="Email" required>
      <input v-model="newMember.phone" type="tel" placeholder="Phone">
      <button type="submit">Save</button>
      <button type="button" @click="handleCancelClick">Cancel</button>
    </form>

    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Name</th>
          <th>Position</th>
          <th>Email</th>
          <th>Phone</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="member in members" :key="member.id">
          <td>
            <div v-show="!member.isEditing">{{ member.id }}</div>
            <div v-show="member.isEditing">{{ member.id }}</div>
          </td>
          <td>
            <div v-show="!member.isEditing">{{ member.name }}</div>
            <input v-show="member.isEditing" v-model="editingMember.name" type="text">
          </td>
          <td>
            <div v-show="!member.isEditing">{{ member.position }}</div>
            <input v-show="member.isEditing" v-model="editingMember.position" type="text">
          </td>
          <td>
            <div v-show="!member.isEditing">{{ member.email }}</div>
            <input v-show="member.isEditing" v-model="editingMember.email" type="email">
          </td>
          <td>
            <div v-show="!member.isEditing">{{ member.phone }}</div>
            <input v-show="member.isEditing" v-model="editingMember.phone" type="tel">
          </td>
          <td>
            <template v-if="!member.isEditing">
              <button @click="startEdit(member)">Edit</button>
              <button @click="deleteMember(member)">Delete</button>
            </template>
            <template v-else>
              <button @click="saveEdit(member)">Save</button>
              <button @click="handleCancelEditClick($event, member)">Cancel</button>
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
  members: Array<{
    id: number;
    name: string;
    position: string;
    email: string;
    phone: string;
    isEditing?: boolean;
    nonce: string;
  }>;
}>();

const showAddForm = ref(false);
const newMember = ref({
  name: '',
  position: '',
  email: '',
  phone: '',
  nonce: ''
});

const editingMember = ref({
  id: 0,
  name: '',
  position: '',
  email: '',
  phone: '',
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
        action: 'cursor_team',
        task: 'team_add',
        name: newMember.value.name,
        position: newMember.value.position,
        email: newMember.value.email,
        phone: newMember.value.phone,
        nonce: newMember.value.nonce
      })
    });

    if (!response.ok) {
      throw new Error('Failed to add member');
    }

    const data = await response.json();
    console.log('Member added:', data);
    newMember.value = {
      name: '',
      position: '',
      email: '',
      phone: '',
      nonce: ''
    };
    showAddForm.value = false;
  } catch (error) {
    alert('Something went wrong while adding the member');
    console.error('Error:', error);
  }
};

const startEdit = (member: typeof props.members[0]) => {
  editingMember.value = { ...member };
  member.isEditing = true;
};

const saveEdit = async (member: typeof props.members[0]) => {
  try {
    const response = await fetch('/wp-admin/admin-ajax.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
      },
      body: new URLSearchParams({
        action: 'cursor_team',
        task: 'team_update',
        id: editingMember.value.id.toString(),
        name: editingMember.value.name,
        position: editingMember.value.position,
        email: editingMember.value.email,
        phone: editingMember.value.phone,
        nonce: editingMember.value.nonce
      })
    });

    if (!response.ok) {
      throw new Error('Failed to update member');
    }

    const data = await response.json();
    console.log('Member updated:', data);
    Object.assign(member, editingMember.value);
    member.isEditing = false;
    editingMember.value = {
      id: 0,
      name: '',
      position: '',
      email: '',
      phone: '',
      nonce: ''
    };
  } catch (error) {
    alert('Something went wrong while updating the member');
    console.error('Error:', error);
  }
};

const handleCancelClick = () => {
  showAddForm.value = false;
  newMember.value = {
    name: '',
    position: '',
    email: '',
    phone: '',
    nonce: ''
  };
};

const handleCancelEditClick = (event: MouseEvent, member: typeof props.members[0]) => {
  member.isEditing = false;
  editingMember.value = {
    id: 0,
    name: '',
    position: '',
    email: '',
    phone: '',
    nonce: ''
  };
};

const deleteMember = async (member: typeof props.members[0]) => {
  if (confirm('Are you sure you want to delete this member?')) {
    try {
      const response = await fetch('/wp-admin/admin-ajax.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
          action: 'cursor_team',
          task: 'team_delete',
          id: member.id.toString(),
          nonce: member.nonce
        })
      });

      if (!response.ok) {
        throw new Error('Failed to delete member');
      }

      const data = await response.json();
      console.log('Member deleted:', data);
    } catch (error) {
      alert('Something went wrong while deleting the member');
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
.team-form {
  margin: 20px 0;
  padding: 15px;
  border: 1px solid #ddd;
  background-color: #f9f9f9;
}
.team-form input {
  margin-right: 10px;
  padding: 5px;
  width: 200px;
}
</style> 