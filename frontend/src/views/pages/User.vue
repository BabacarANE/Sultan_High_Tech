<script setup>
import { FilterMatchMode } from 'primevue/api';
import { ref, onMounted, onBeforeMount } from 'vue';
import { useToast } from 'primevue/usetoast';
import axios from 'axios';

const toast = useToast();

const users = ref(null);
const roles = ref([]); // Define roles as a ref
const userDialog = ref(false);
const deleteUserDialog = ref(false);
const deleteUsersDialog = ref(false);
const user = ref({});
const selectedUsers = ref(null);
const dt = ref(null);
const filters = ref({});
const submitted = ref(false);
const validationErrors = ref({}); // To store validation errors

onBeforeMount(() => {
    initFilters();
});

onMounted(() => {
    fetchUsers();
    fetchRoles();
});

const fetchUsers = async () => {
    try {
        const response = await axios.get('http://127.0.0.1:8000/api/users');
        users.value = response.data;
    } catch (error) {
        console.error('Error fetching users:', error);
        toast.add({ severity: 'error', summary: 'Error', detail: 'Failed to fetch users', life: 3000 });
    }
};

const fetchRoles = async () => {
    try {
        const response = await axios.get('http://127.0.0.1:8000/api/roles');
        roles.value = response.data.map(role => ({
            id: role.id,
            name: role.name
        }));
        console.log('Fetched roles:', roles.value); // Log fetched roles
    } catch (error) {
        console.error('Error fetching roles:', error);
        toast.add({ severity: 'error', summary: 'Error', detail: 'Failed to fetch roles', life: 3000 });
    }
};

const formatDate = (value) => {
    return new Date(value).toLocaleString();
};

const openNew = () => {
    user.value = {
        nom: '',
        prenom: '',
        email: '',
        role_id: null
    };
    validationErrors.value = {}; // Clear previous validation errors
    submitted.value = false;
    userDialog.value = true;
};

const hideDialog = () => {
    userDialog.value = false;
    submitted.value = false;
};

const saveUser = async () => {
    submitted.value = true;

    // Check all required fields
    if (!user.value.nom || !user.value.prenom || !user.value.email || !user.value.role_id) {
        toast.add({severity: 'error', summary: 'Error', detail: 'Please fill all required fields', life: 3000});
        return;
    }

    try {
        let response;
        const userData = {
            nom: user.value.nom,
            prenom: user.value.prenom,
            email: user.value.email,
            password: 'passer123', // Default password
            role_id: user.value.role_id
        };

        console.log('User data before sending:', user.value);
        console.log('User data being sent:', userData);

        if (user.value.id) {
            // Update existing user
            console.log('Updating user:', userData);
            response = await axios.put(`http://127.0.0.1:8000/api/users/${user.value.id}`, userData);
        } else {
            // Create new user
            console.log('Creating new user:', userData);
            response = await axios.post('http://127.0.0.1:8000/api/users', userData);
        }

        console.log('API Response:', response.data);

        if (response.data) {
            toast.add({severity: 'success', summary: 'Successful', detail: user.value.id ? 'User Updated' : 'User Created', life: 3000});
            userDialog.value = false;
            user.value = {};
            validationErrors.value = {};
            await fetchUsers(); // Refresh the user list
        } else {
            toast.add({severity: 'error', summary: 'Error', detail: 'Failed to save user', life: 3000});
        }
    } catch (error) {
        console.error('Error saving user:', error);
        if (error.response && error.response.data && error.response.data.errors) {
            validationErrors.value = error.response.data.errors;
            console.log('Validation errors:', error.response.data.errors);
            let errorMessage = 'Validation errors:';
            for (let field in error.response.data.errors) {
                errorMessage += `\n${field}: ${error.response.data.errors[field].join(', ')}`;
            }
            toast.add({severity: 'error', summary: 'Validation Error', detail: errorMessage, life: 5000});
        } else {
            toast.add({severity: 'error', summary: 'Error', detail: 'Failed to save user: ' + (error.message || 'Unknown error'), life: 3000});
        }
    }
};

const editUser = (editUser) => {
    user.value = {...editUser};
    validationErrors.value = {}; // Clear previous validation errors
    userDialog.value = true;
};

const confirmDeleteUser = (editUser) => {
    user.value = editUser;
    deleteUserDialog.value = true;
};

const deleteUser = async () => {
    try {
        await axios.delete(`http://127.0.0.1:8000/api/users/${user.value.id}`);
        users.value = users.value.filter((val) => val.id !== user.value.id);
        deleteUserDialog.value = false;
        user.value = {};
        toast.add({severity: 'success', summary: 'Successful', detail: 'User Deleted', life: 3000});
    } catch (error) {
        console.error('Error deleting user:', error);
        toast.add({severity: 'error', summary: 'Error', detail: 'Failed to delete user', life: 3000});
    }
};

const exportCSV = () => {
    dt.value.exportCSV();
};

const confirmDeleteSelected = () => {
    deleteUsersDialog.value = true;
};

const deleteSelectedUsers = async () => {
    try {
        for (let user of selectedUsers.value) {
            await axios.delete(`http://127.0.0.1:8000/api/users/${user.id}`);
        }
        users.value = users.value.filter((val) => !selectedUsers.value.includes(val));
        deleteUsersDialog.value = false;
        selectedUsers.value = null;
        toast.add({severity: 'success', summary: 'Successful', detail: 'Users Deleted', life: 3000});
    } catch (error) {
        console.error('Error deleting selected users:', error);
        toast.add({severity: 'error', summary: 'Error', detail: 'Failed to delete selected users', life: 3000});
    }
};

const initFilters = () => {
    filters.value = {
        global: {value: null, matchMode: FilterMatchMode.CONTAINS}
    };
};
</script>

<template>
    <div class="grid">
        <div class="col-12">
            <div class="card">
                <Toolbar class="mb-4">
                    <template v-slot:start>
                        <div class="my-2">
                            <Button label="New" icon="pi pi-plus" class="mr-2" severity="success" @click="openNew"/>
                            <Button label="Delete" icon="pi pi-trash" severity="danger" @click="confirmDeleteSelected"
                                    :disabled="!selectedUsers || !selectedUsers.length"/>
                        </div>
                    </template>

                    <template v-slot:end>
                        <Button label="Export" icon="pi pi-upload" severity="help" @click="exportCSV($event)"/>
                    </template>
                </Toolbar>

                <DataTable ref="dt" :value="users" v-model:selection="selectedUsers" dataKey="id"
                           :paginator="true" :rows="10" :filters="filters"
                           paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                           :rowsPerPageOptions="[5, 10, 25]"
                           currentPageReportTemplate="Showing {first} to {last} of {totalRecords} users">
                    <template #header>
                        <div class="flex flex-column md:flex-row md:justify-content-between md:align-items-center">
                            <h5 class="m-0">Manage Users</h5>
                            <span class="block mt-2 md:mt-0 p-input-icon-left">
                                <i class="pi pi-search"/>
                                <InputText v-model="filters['global'].value" placeholder="Search..."/>
                            </span>
                        </div>
                    </template>

                    <Column selectionMode="multiple" headerStyle="width: 3rem"></Column>
                    <Column field="id" header="ID" :sortable="true" headerStyle="width:10%; min-width:8rem;">
                        <template #body="slotProps">
                            <span class="p-column-title">ID</span>
                            {{ slotProps.data.id }}
                        </template>
                    </Column>
                    <Column field="nom" header="Nom" :sortable="true" headerStyle="width:20%; min-width:10rem;">
                        <template #body="slotProps">
                            <span class="p-column-title">Nom</span>
                            {{ slotProps.data.nom }}
                        </template>
                    </Column>
                    <Column field="prenom" header="Prénom" :sortable="true" headerStyle="width:20%; min-width:10rem;">
                        <template #body="slotProps">
                            <span class="p-column-title">Prénom</span>
                            {{ slotProps.data.prenom }}
                        </template>
                    </Column>
                    <Column field="email" header="Email" :sortable="true" headerStyle="width:25%; min-width:10rem;">
                        <template #body="slotProps">
                            <span class="p-column-title">Email</span>
                            {{ slotProps.data.email }}
                        </template>
                    </Column>
                    <Column field="role_id" header="Role" :sortable="true" headerStyle="width:15%; min-width:8rem;">
                        <template #body="slotProps">
                            <span class="p-column-title">Role</span>
                            {{ slotProps.data.role_id }}
                        </template>
                    </Column>
                    <Column header="Actions" headerStyle="width: 10rem">
                        <template #body="slotProps">
                            <Button icon="pi pi-pencil" @click="editUser(slotProps.data)"/>
                            <Button icon="pi pi-trash" @click="confirmDeleteUser(slotProps.data)"/>
                        </template>
                    </Column>
                </DataTable>

                <Dialog v-model:visible="userDialog" :style="{width: '450px'}" header="User Details" modal
                        class="p-fluid">
                    <div class="field">
                        <label for="nom">Nom</label>
                        <InputText id="nom" v-model="user.nom" :class="{'p-invalid': submitted && !user.nom}"/>
                        <small v-if="submitted && !user.nom" class="p-invalid">Nom is required.</small>
                        <small v-if="validationErrors.nom" class="p-invalid">{{ validationErrors.nom[0] }}</small>
                    </div>

                    <div class="field">
                        <label for="prenom">Prénom</label>
                        <InputText id="prenom" v-model="user.prenom" :class="{'p-invalid': submitted && !user.prenom}"/>
                        <small v-if="submitted && !user.prenom" class="p-invalid">Prénom is required.</small>
                        <small v-if="validationErrors.prenom" class="p-invalid">{{ validationErrors.prenom[0] }}</small>
                    </div>

                    <div class="field">
                        <label for="email">Email</label>
                        <InputText id="email" v-model="user.email" :class="{'p-invalid': submitted && !user.email}"/>
                        <small v-if="submitted && !user.email" class="p-invalid">Email is required.</small>
                        <small v-if="validationErrors.email" class="p-invalid">{{ validationErrors.email[0] }}</small>
                    </div>

                    <div class="field">
                        <label for="role">Role</label>
                        <Dropdown id="role" v-model="user.role_id" :options="roles" optionLabel="name" optionValue="id"
                                  placeholder="Select a Role" :class="{'p-invalid': submitted && !user.role_id}"/>
                        <small v-if="submitted && !user.role_id" class="p-error">Role is required.</small>
                        <small v-if="validationErrors.role_id" class="p-invalid">{{ validationErrors.role_id[0] }}</small>
                    </div>

                    <div class="flex justify-content-end mt-3">
                        <Button label="Cancel" icon="pi pi-times" class="p-button-text" @click="hideDialog"/>
                        <Button label="Save" icon="pi pi-check" class="p-button-text" @click="saveUser"/>
                    </div>
                </Dialog>

                <Dialog v-model:visible="deleteUserDialog" :style="{width: '450px'}" header="Confirm"
                        modal :footer="deleteUserDialogFooter" class="p-fluid">
                    <span>Are you sure you want to delete <b>{{ user.nom }} {{ user.prenom }}</b>?</span>
                </Dialog>

                <Dialog v-model:visible="deleteUsersDialog" :style="{width: '450px'}" header="Confirm" modal
                        :footer="deleteUsersDialogFooter" class="p-fluid">
                    <span>Are you sure you want to delete the selected users?</span>
                </Dialog>
            </div>
        </div>
    </div>
</template>
