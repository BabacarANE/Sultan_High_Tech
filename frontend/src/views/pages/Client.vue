<script setup>
import { FilterMatchMode } from 'primevue/api';
import { ref, onMounted, onBeforeMount } from 'vue';
import { useToast } from 'primevue/usetoast';
import axios from 'axios';

const toast = useToast();

const clients = ref(null);
const clientDialog = ref(false);
const deleteClientDialog = ref(false);
const deleteClientsDialog = ref(false);
const client = ref({});
const selectedClients = ref(null);
const dt = ref(null);
const filters = ref({});
const submitted = ref(false);

onBeforeMount(() => {
    initFilters();
});

onMounted(() => {
    fetchClients();
});

const fetchClients = async () => {
    try {
        const response = await axios.get('http://127.0.0.1:8000/api/clients');
        clients.value = response.data;
    } catch (error) {
        console.error('Error fetching clients:', error);
        toast.add({ severity: 'error', summary: 'Error', detail: 'Failed to fetch clients', life: 3000 });
    }
};

const formatDate = (value) => {
    return new Date(value).toLocaleString();
};

const openNew = () => {
    client.value = { sexe: 'M' }; // Default value for sex
    submitted.value = false;
    clientDialog.value = true;
};

const hideDialog = () => {
    clientDialog.value = false;
    submitted.value = false;
};

const saveClient = async () => {
    submitted.value = true;
    if (client.value.nom && client.value.prenom && client.value.numero_telephone) {
        try {
            if (client.value.id) {
                // Update existing client
                await axios.post(`http://127.0.0.1:8000/api/clients/${client.value.id}`, client.value);
                toast.add({ severity: 'success', summary: 'Successful', detail: 'Client Updated', life: 3000 });
            } else {
                // Create new client
                await axios.post('http://127.0.0.1:8000/api/clients', client.value);
                toast.add({ severity: 'success', summary: 'Successful', detail: 'Client Created', life: 3000 });
            }
            clientDialog.value = false;
            client.value = {};
            await fetchClients(); // Refresh the client list
        } catch (error) {
            console.error('Error saving client:', error);
            toast.add({ severity: 'error', summary: 'Error', detail: 'Failed to save client', life: 3000 });
        }
    }
};

const editClient = (editClient) => {
    client.value = { ...editClient };
    clientDialog.value = true;
};

const confirmDeleteClient = (editClient) => {
    client.value = editClient;
    deleteClientDialog.value = true;
};

const deleteClient = async () => {
    try {
        await axios.delete(`http://127.0.0.1:8000/api/clients/${client.value.id}`);
        clients.value = clients.value.filter((val) => val.id !== client.value.id);
        deleteClientDialog.value = false;
        client.value = {};
        toast.add({ severity: 'success', summary: 'Successful', detail: 'Client Deleted', life: 3000 });
    } catch (error) {
        console.error('Error deleting client:', error);
        toast.add({ severity: 'error', summary: 'Error', detail: 'Failed to delete client', life: 3000 });
    }
};

const exportCSV = () => {
    dt.value.exportCSV();
};

const confirmDeleteSelected = () => {
    deleteClientsDialog.value = true;
};

const deleteSelectedClients = async () => {
    try {
        for (let client of selectedClients.value) {
            await axios.delete(`http://127.0.0.1:8000/api/clients/${client.id}`);
        }
        clients.value = clients.value.filter((val) => !selectedClients.value.includes(val));
        deleteClientsDialog.value = false;
        selectedClients.value = null;
        toast.add({ severity: 'success', summary: 'Successful', detail: 'Clients Deleted', life: 3000 });
    } catch (error) {
        console.error('Error deleting selected clients:', error);
        toast.add({ severity: 'error', summary: 'Error', detail: 'Failed to delete selected clients', life: 3000 });
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


                    <template v-slot:end>
                        <Button label="Export" icon="pi pi-upload" severity="help" @click="exportCSV($event)" />
                    </template>
                </Toolbar>

                <DataTable ref="dt" :value="clients" v-model:selection="selectedClients" dataKey="id"
                           :paginator="true" :rows="10" :filters="filters"
                           paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                           :rowsPerPageOptions="[5, 10, 25]"
                           currentPageReportTemplate="Showing {first} to {last} of {totalRecords} clients">
                    <template #header>
                        <div class="flex flex-column md:flex-row md:justify-content-between md:align-items-center">
                            <h5 class="m-0">Manage Clients</h5>
                            <span class="block mt-2 md:mt-0 p-input-icon-left">
                                <i class="pi pi-search" />
                                <InputText v-model="filters['global'].value" placeholder="Search..." />
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
                    <Column field="nom" header="Nom" :sortable="true" headerStyle="width:15%; min-width:10rem;">
                        <template #body="slotProps">
                            <span class="p-column-title">Nom</span>
                            {{ slotProps.data.nom }}
                        </template>
                    </Column>
                    <Column field="prenom" header="Prénom" :sortable="true" headerStyle="width:15%; min-width:10rem;">
                        <template #body="slotProps">
                            <span class="p-column-title">Prénom</span>
                            {{ slotProps.data.prenom }}
                        </template>
                    </Column>
                    <Column field="adresse" header="Adresse" :sortable="true" headerStyle="width:20%; min-width:10rem;">
                        <template #body="slotProps">
                            <span class="p-column-title">Adresse</span>
                            {{ slotProps.data.adresse }}
                        </template>
                    </Column>
                    <Column field="numero_telephone" header="Téléphone" :sortable="true" headerStyle="width:15%; min-width:10rem;">
                        <template #body="slotProps">
                            <span class="p-column-title">Téléphone</span>
                            {{ slotProps.data.numero_telephone }}
                        </template>
                    </Column>
                    <Column field="sexe" header="Sexe" :sortable="true" headerStyle="width:10%; min-width:8rem;">
                        <template #body="slotProps">
                            <span class="p-column-title">Sexe</span>
                            {{ slotProps.data.sexe }}
                        </template>
                    </Column>
                    <Column headerStyle="min-width:10rem;">
                        <template #body="slotProps">
                            <Button icon="pi pi-pencil" class="p-button-rounded p-button-success mr-2" @click="editClient(slotProps.data)" />
                            <Button icon="pi pi-trash" class="p-button-rounded p-button-warning" @click="confirmDeleteClient(slotProps.data)" />
                        </template>
                    </Column>
                </DataTable>

                <Dialog v-model:visible="clientDialog" :style="{width: '450px'}" header="Client Details" :modal="true" class="p-fluid">
                    <div class="field">
                        <label for="nom">Nom</label>
                        <InputText id="nom" v-model.trim="client.nom" required="true" autofocus :class="{'p-invalid': submitted && !client.nom}" />
                        <small class="p-invalid" v-if="submitted && !client.nom">Nom is required.</small>
                    </div>
                    <div class="field">
                        <label for="prenom">Prénom</label>
                        <InputText id="prenom" v-model.trim="client.prenom" required="true" :class="{'p-invalid': submitted && !client.prenom}" />
                        <small class="p-invalid" v-if="submitted && !client.prenom">Prénom is required.</small>
                    </div>
                    <div class="field">
                        <label for="adresse">Adresse</label>
                        <InputText id="adresse" v-model.trim="client.adresse" />
                    </div>
                    <div class="field">
                        <label for="numero_telephone">Téléphone</label>
                        <InputText id="numero_telephone" v-model.trim="client.numero_telephone" required="true" :class="{'p-invalid': submitted && !client.numero_telephone}" />
                        <small class="p-invalid" v-if="submitted && !client.numero_telephone">Téléphone is required.</small>
                    </div>
                    <div class="field">
                        <label for="sexe">Sexe</label>
                        <Dropdown id="sexe" v-model="client.sexe" :options="['M', 'F']" placeholder="Select Sex" />
                    </div>
                    <template #footer>
                        <Button label="Cancel" icon="pi pi-times" class="p-button-text" @click="hideDialog" />
                        <Button label="Save" icon="pi pi-check" class="p-button-text" @click="saveClient" />
                    </template>
                </Dialog>

                <Dialog v-model:visible="deleteClientDialog" :style="{width: '450px'}" header="Confirm" :modal="true">
                    <div class="flex align-items-center justify-content-center">
                        <i class="pi pi-exclamation-triangle mr-3" style="font-size: 2rem" />
                        <span v-if="client">Are you sure you want to delete <b>{{client.nom}} {{client.prenom}}</b>?</span>
                    </div>
                    <template #footer>
                        <Button label="No" icon="pi pi-times" class="p-button-text" @click="deleteClientDialog = false" />
                        <Button label="Yes" icon="pi pi-check" class="p-button-text" @click="deleteClient" />
                    </template>
                </Dialog>

                <Dialog v-model:visible="deleteClientsDialog" :style="{width: '450px'}" header="Confirm" :modal="true">
                    <div class="flex align-items-center justify-content-center">
                        <i class="pi pi-exclamation-triangle mr-3" style="font-size: 2rem" />
                        <span v-if="client">Are you sure you want to delete the selected clients?</span>
                    </div>
                    <template #footer>
                        <Button label="No" icon="pi pi-times" class="p-button-text" @click="deleteClientsDialog = false" />
                        <Button label="Yes" icon="pi pi-check" class="p-button-text" @click="deleteSelectedClients" />
                    </template>
                </Dialog>
            </div>
        </div>
    </div>
</template>
