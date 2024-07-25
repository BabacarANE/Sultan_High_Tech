<script setup>
import { FilterMatchMode } from 'primevue/api';
import { ref, onMounted, onBeforeMount, computed } from 'vue';
import { useToast } from 'primevue/usetoast';
import axios from 'axios';
import { useStore } from 'vuex';

const toast = useToast();
const store = useStore();

const orders = ref(null);
const orderDetailsDialog = ref(false);
const selectedOrder = ref(null);
const dt = ref(null);
const filters = ref({});

// Utilisation de computed pour obtenir les informations du client
const storeClient = computed(() => store.getters['user/getClient']);

// Computed property pour obtenir l'ID du client
const clientId = computed(() => storeClient.value?.id);

onBeforeMount(() => {
    initFilters();
});

onMounted(() => {
    fetchUserOrders();
});

const fetchUserOrders = async () => {
    // Récupération des informations du client depuis le store
    const storeClient = store.getters['user/getClient'];
    const storeUser = store.getters['user/getUser'];
    // Affichage du contenu de storeClient et storeUser dans la console
    console.log('storeClient:', storeClient);
    console.log('storeUser:', storeUser);
    const clientId=storeUser?.id;
    console.log('userid:', clientId);
    if (!clientId) {
        console.error('Client ID is not available');
        toast.add({severity: 'error', summary: 'Error', detail: 'User data not loaded', life: 3000});
        return;
    }

    try {
        const response = await axios.get(`http://127.0.0.1:8000/api/clients/${clientId}/orders`);
        orders.value = response.data;
    } catch (error) {
        console.error('Error fetching user orders:', error);
        toast.add({severity: 'error', summary: 'Error', detail: 'Failed to fetch orders', life: 3000});
    }
};

const formatDate = (value) => {
    return new Date(value).toLocaleString();
};

const exportCSV = () => {
    dt.value.exportCSV();
};

const initFilters = () => {
    filters.value = {
        global: {value: null, matchMode: FilterMatchMode.CONTAINS}
    };
};

const showOrderDetails = (order) => {
    selectedOrder.value = order;
    orderDetailsDialog.value = true;
};

</script>

<template>
    <div class="grid">
        <div class="col-12">
            <div class="card">
                <Toolbar class="mb-4">
                    <template v-slot:end>
                        <Button label="Export" icon="pi pi-upload" severity="help" @click="exportCSV($event)"/>
                    </template>
                </Toolbar>

                <DataTable ref="dt" :value="orders" dataKey="id"
                           :paginator="true" :rows="10" :filters="filters"
                           paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                           :rowsPerPageOptions="[5, 10, 25]"
                           currentPageReportTemplate="Showing {first} to {last} of {totalRecords} orders">
                    <template #header>
                        <div class="flex flex-column md:flex-row md:justify-content-between md:align-items-center">
                            <h5 class="m-0">My Order History</h5>
                            <span class="block mt-2 md:mt-0 p-input-icon-left">
                                <i class="pi pi-search"/>
                                <InputText v-model="filters['global'].value" placeholder="Search..."/>
                            </span>
                        </div>
                    </template>

                    <Column field="id" header="Order ID" :sortable="true" headerStyle="width:10%; min-width:8rem;">
                        <template #body="slotProps">
                            <span class="p-column-title">Order ID</span>
                            {{ slotProps.data.id }}
                        </template>
                    </Column>
                    <Column field="statut" header="Status" :sortable="true" headerStyle="width:15%; min-width:10rem;">
                        <template #body="slotProps">
                            <span class="p-column-title">Status</span>
                            {{ slotProps.data.statut }}
                        </template>
                    </Column>
                    <Column field="date_commande" header="Order Date" :sortable="true"
                            headerStyle="width:20%; min-width:10rem;">
                        <template #body="slotProps">
                            <span class="p-column-title">Order Date</span>
                            {{ formatDate(slotProps.data.date_commande) }}
                        </template>
                    </Column>
                    <Column field="montant_total" header="Total Amount" :sortable="true"
                            headerStyle="width:15%; min-width:10rem;">
                        <template #body="slotProps">
                            <span class="p-column-title">Total Amount</span>
                            {{ slotProps.data.montant_total }}
                        </template>
                    </Column>
                    <Column headerStyle="min-width:10rem;">
                        <template #body="slotProps">
                            <Button icon="pi pi-eye" class="p-button-rounded p-button-info mr-2"
                                    @click="showOrderDetails(slotProps.data)"/>
                        </template>
                    </Column>
                </DataTable>

                <Dialog v-model:visible="orderDetailsDialog" :style="{width: '450px'}" header="Order Details"
                        :modal="true" class="p-fluid">
                    <div v-if="selectedOrder">
                        <h3>Order #{{ selectedOrder.id }}</h3>
                        <p><strong>Status:</strong> {{ selectedOrder.statut }}</p>
                        <p><strong>Order Date:</strong> {{ formatDate(selectedOrder.date_commande) }}</p>
                        <p><strong>Total Amount:</strong> {{ selectedOrder.montant_total }}</p>
                        <h4>Products:</h4>
                        <ul>
                            <li v-for="product in selectedOrder.products" :key="product.id">
                                {{ product.nom }} - Quantity: {{ product.pivot.quantite }} - Unit Price:
                                {{ product.pivot.prix_unitaire }}
                            </li>
                        </ul>
                    </div>
                    <template #footer>
                        <Button label="Close" icon="pi pi-times" class="p-button-text"
                                @click="orderDetailsDialog = false"/>
                    </template>
                </Dialog>

            </div>
        </div>
    </div>
</template>
