<script setup>
import { FilterMatchMode } from 'primevue/api';
import { ref, onMounted, onBeforeMount } from 'vue';
import { useToast } from 'primevue/usetoast';
import axios from 'axios';

const toast = useToast();

const roles = ref(null);
const roleDialog = ref(false);
const deleteRoleDialog = ref(false);
const deleteRolesDialog = ref(false);
const role = ref({});
const selectedRoles = ref(null);
const dt = ref(null);
const filters = ref({});
const submitted = ref(false);

onBeforeMount(() => {
    initFilters();
});

onMounted(() => {
    fetchRoles();
});

const fetchRoles = async () => {
    try {
        const response = await axios.get('http://127.0.0.1:8000/api/roles');
        roles.value = response.data;
    } catch (error) {
        console.error('Error fetching roles:', error);
        toast.add({ severity: 'error', summary: 'Error', detail: 'Failed to fetch roles', life: 3000 });
    }
};

const formatDate = (value) => {
    return new Date(value).toLocaleString();
};

const openNew = () => {
    role.value = {};
    submitted.value = false;
    roleDialog.value = true;
};

const hideDialog = () => {
    roleDialog.value = false;
    submitted.value = false;
};

const saveRole = async () => {
    submitted.value = true;
    if (role.value.name && role.value.name.trim()) {
        try {
            if (role.value.id) {
                // Update existing role
                await axios.post(`http://127.0.0.1:8000/api/roles/${role.value.id}`, role.value);
                toast.add({ severity: 'success', summary: 'Successful', detail: 'Role Updated', life: 3000 });
            } else {
                // Create new role
                await axios.post('http://127.0.0.1:8000/api/roles', role.value);
                toast.add({ severity: 'success', summary: 'Successful', detail: 'Role Created', life: 3000 });
            }
            roleDialog.value = false;
            role.value = {};
            await fetchRoles(); // Refresh the role list
        } catch (error) {
            console.error('Error saving role:', error);
            toast.add({ severity: 'error', summary: 'Error', detail: 'Failed to save role', life: 3000 });
        }
    }
};

const editRole = (editRole) => {
    role.value = { ...editRole };
    roleDialog.value = true;
};

const confirmDeleteRole = (editRole) => {
    role.value = editRole;
    deleteRoleDialog.value = true;
};

const deleteRole = async () => {
    try {
        await axios.delete(`http://127.0.0.1:8000/api/roles/${role.value.id}`);
        roles.value = roles.value.filter((val) => val.id !== role.value.id);
        deleteRoleDialog.value = false;
        role.value = {};
        toast.add({ severity: 'success', summary: 'Successful', detail: 'Role Deleted', life: 3000 });
    } catch (error) {
        console.error('Error deleting role:', error);
        toast.add({ severity: 'error', summary: 'Error', detail: 'Failed to delete role', life: 3000 });
    }
};

const exportCSV = () => {
    dt.value.exportCSV();
};

const confirmDeleteSelected = () => {
    deleteRolesDialog.value = true;
};

const deleteSelectedRoles = async () => {
    try {
        for (let role of selectedRoles.value) {
            await axios.delete(`http://127.0.0.1:8000/api/roles/${role.id}`);
        }
        roles.value = roles.value.filter((val) => !selectedRoles.value.includes(val));
        deleteRolesDialog.value = false;
        selectedRoles.value = null;
        toast.add({ severity: 'success', summary: 'Successful', detail: 'Roles Deleted', life: 3000 });
    } catch (error) {
        console.error('Error deleting selected roles:', error);
        toast.add({ severity: 'error', summary: 'Error', detail: 'Failed to delete selected roles', life: 3000 });
    }
};

const initFilters = () => {
    filters.value = {
        global: { value: null, matchMode: FilterMatchMode.CONTAINS }
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
                            <Button label="New" icon="pi pi-plus" class="mr-2" severity="success" @click="openNew" />
                            <Button label="Delete" icon="pi pi-trash" severity="danger" @click="confirmDeleteSelected"
                                    :disabled="!selectedRoles || !selectedRoles.length" />
                        </div>
                    </template>

                    <template v-slot:end>
                        <Button label="Export" icon="pi pi-upload" severity="help" @click="exportCSV($event)" />
                    </template>
                </Toolbar>

                <DataTable ref="dt" :value="roles" v-model:selection="selectedRoles" dataKey="id"
                           :paginator="true" :rows="10" :filters="filters"
                           paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                           :rowsPerPageOptions="[5, 10, 25]"
                           currentPageReportTemplate="Showing {first} to {last} of {totalRecords} roles">
                    <template #header>
                        <div class="flex flex-column md:flex-row md:justify-content-between md:align-items-center">
                            <h5 class="m-0">Manage Roles</h5>
                            <span class="block mt-2 md:mt-0 p-input-icon-left">
                                <i class="pi pi-search" />
                                <InputText v-model="filters['global'].value" placeholder="Search..." />
                            </span>
                        </div>
                    </template>

                    <Column selectionMode="multiple" headerStyle="width: 3rem"></Column>
                    <Column field="id" header="ID" :sortable="true" headerStyle="width:15%; min-width:8rem;">
                        <template #body="slotProps">
                            <span class="p-column-title">ID</span>
                            {{ slotProps.data.id }}
                        </template>
                    </Column>
                    <Column field="name" header="Name" :sortable="true" headerStyle="width:35%; min-width:10rem;">
                        <template #body="slotProps">
                            <span class="p-column-title">Name</span>
                            {{ slotProps.data.name }}
                        </template>
                    </Column>
                    <Column field="created_at" header="Created At" :sortable="true" headerStyle="width:20%; min-width:10rem;">
                        <template #body="slotProps">
                            <span class="p-column-title">Created At</span>
                            {{ formatDate(slotProps.data.created_at) }}
                        </template>
                    </Column>
                    <Column field="updated_at" header="Updated At" :sortable="true" headerStyle="width:20%; min-width:10rem;">
                        <template #body="slotProps">
                            <span class="p-column-title">Updated At</span>
                            {{ formatDate(slotProps.data.updated_at) }}
                        </template>
                    </Column>
                    <Column headerStyle="min-width:10rem;">
                        <template #body="slotProps">
                            <Button icon="pi pi-pencil" class="p-button-rounded p-button-success mr-2" @click="editRole(slotProps.data)" />
                            <Button icon="pi pi-trash" class="p-button-rounded p-button-warning" @click="confirmDeleteRole(slotProps.data)" />
                        </template>
                    </Column>
                </DataTable>

                <Dialog v-model:visible="roleDialog" :style="{width: '450px'}" header="Role Details" :modal="true" class="p-fluid">
                    <div class="field">
                        <label for="name">Name</label>
                        <InputText id="name" v-model.trim="role.name" required="true" autofocus :class="{'p-invalid': submitted && !role.name}" />
                        <small class="p-invalid" v-if="submitted && !role.name">Name is required.</small>
                    </div>
                    <template #footer>
                        <Button label="Cancel" icon="pi pi-times" class="p-button-text" @click="hideDialog" />
                        <Button label="Save" icon="pi pi-check" class="p-button-text" @click="saveRole" />
                    </template>
                </Dialog>

                <Dialog v-model:visible="deleteRoleDialog" :style="{width: '450px'}" header="Confirm" :modal="true">
                    <div class="flex align-items-center justify-content-center">
                        <i class="pi pi-exclamation-triangle mr-3" style="font-size: 2rem" />
                        <span v-if="role">Are you sure you want to delete <b>{{role.name}}</b>?</span>
                    </div>
                    <template #footer>
                        <Button label="No" icon="pi pi-times" class="p-button-text" @click="deleteRoleDialog = false" />
                        <Button label="Yes" icon="pi pi-check" class="p-button-text" @click="deleteRole" />
                    </template>
                </Dialog>

                <Dialog v-model:visible="deleteRolesDialog" :style="{width: '450px'}" header="Confirm" :modal="true">
                    <div class="flex align-items-center justify-content-center">
                        <i class="pi pi-exclamation-triangle mr-3" style="font-size: 2rem" />
                        <span v-if="role">Are you sure you want to delete the selected roles?</span>
                    </div>
                    <template #footer>
                        <Button label="No" icon="pi pi-times" class="p-button-text" @click="deleteRolesDialog = false" />
                        <Button label="Yes" icon="pi pi-check" class="p-button-text" @click="deleteSelectedRoles" />
                    </template>
                </Dialog>
            </div>
        </div>
    </div>
</template>
