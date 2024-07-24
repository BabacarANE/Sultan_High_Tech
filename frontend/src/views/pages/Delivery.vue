<script setup>
import { FilterMatchMode } from 'primevue/api';
import { ref, onMounted, onBeforeMount } from 'vue';
import { useToast } from 'primevue/usetoast';
import Button from 'primevue/button';
import axios from 'axios';

const toast = useToast();

const orders = ref(null);
const orderDialog = ref(false);
const orderDetailsDialog = ref(false);
const selectedOrder = ref(null);
const dt = ref(null);
const filters = ref({});

onBeforeMount(() => {
    initFilters();
});

onMounted(() => {
    fetchOrders();
});

const fetchOrders = async () => {
    try {
        const response = await axios.get('http://127.0.0.1:8000/api/orders');
        orders.value = response.data.filter(order => order.statut === 'validé');
        // Fetch client information for each order
        for (let order of orders.value) {
            await fetchClientInfo(order);
        }
    } catch (error) {
        console.error('Error fetching orders:', error);
        toast.add({ severity: 'error', summary: 'Error', detail: 'Failed to fetch orders', life: 3000 });
    }
};

const fetchClientInfo = async (order) => {
    try {
        const response = await axios.get(`http://127.0.0.1:8000/api/clients/${order.client_id}`);
        order.client = response.data;
    } catch (error) {
        console.error('Error fetching client info:', error);
        order.client = { nom: 'N/A', prenom: 'N/A' };
    }
};

const markAsDelivered = async () => {
    if (selectedOrder.value) {
        try {
            await axios.post(`http://127.0.0.1:8000/api/orders/${selectedOrder.value.id}/deliver`);
            selectedOrder.value.statut = 'livré';
            toast.add({ severity: 'success', summary: 'Successful', detail: 'Order Marked as Delivered', life: 3000 });
            await fetchOrders(); // Refresh the orders list
        } catch (error) {
            console.error('Error marking order as delivered:', error);
            toast.add({ severity: 'error', summary: 'Error', detail: 'Failed to mark order as delivered', life: 3000 });
        }
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
        global: { value: null, matchMode: FilterMatchMode.CONTAINS }
    };
};

const showOrderDetails = (order) => {
    selectedOrder.value = order;
    orderDetailsDialog.value = true;
};
const viewFile = (filePath) => {
    const baseUrl = 'http://127.0.0.1:8000/storage/'; // Ajustez cette URL pour qu'elle corresponde à votre backend
    const fullPath = baseUrl + filePath.replace('app/', '');
    window.open(fullPath, '_blank');
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

                <DataTable ref="dt" :value="orders" dataKey="id"
                           :paginator="true" :rows="10" :filters="filters"
                           paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                           :rowsPerPageOptions="[5, 10, 25]"
                           currentPageReportTemplate="Showing {first} to {last} of {totalRecords} validated orders">
                    <template #header>
                        <div class="flex flex-column md:flex-row md:justify-content-between md:align-items-center">
                            <h5 class="m-0">Manage Validated Orders</h5>
                            <span class="block mt-2 md:mt-0 p-input-icon-left">
                                <i class="pi pi-search" />
                                <InputText v-model="filters['global'].value" placeholder="Search..." />
                            </span>
                        </div>
                    </template>

                    <Column field="id" header="ID" :sortable="true" headerStyle="width:10%; min-width:8rem;">
                        <template #body="slotProps">
                            <span class="p-column-title">ID</span>
                            {{ slotProps.data.id }}
                        </template>
                    </Column>
                    <Column field="client_name" header="Client" :sortable="true" headerStyle="width:20%; min-width:10rem;">
                        <template #body="slotProps">
                            <span class="p-column-title">Client</span>
                            {{ slotProps.data.client ? `${slotProps.data.client.prenom} ${slotProps.data.client.nom}` : 'Loading...' }}
                        </template>
                    </Column>
                    <Column field="statut" header="Status" :sortable="true" headerStyle="width:15%; min-width:10rem;">
                        <template #body="slotProps">
                            <span class="p-column-title">Status</span>
                            {{ slotProps.data.statut }}
                        </template>
                    </Column>
                    <Column field="date_commande" header="Order Date" :sortable="true" headerStyle="width:20%; min-width:10rem;">
                        <template #body="slotProps">
                            <span class="p-column-title">Order Date</span>
                            {{ formatDate(slotProps.data.date_commande) }}
                        </template>
                    </Column>
                    <Column field="montant_total" header="Total Amount" :sortable="true" headerStyle="width:15%; min-width:10rem;">
                        <template #body="slotProps">
                            <span class="p-column-title">Total Amount</span>
                            {{ slotProps.data.montant_total }}
                        </template>
                    </Column>
                    <Column headerStyle="min-width:10rem;">
                        <template #body="slotProps">
                            <Button icon="pi pi-eye" class="p-button-rounded p-button-info mr-2" @click="showOrderDetails(slotProps.data)" />
                        </template>
                    </Column>
                </DataTable>

                <Dialog v-model:visible="orderDetailsDialog" :style="{width: '450px'}" header="Order Details" :modal="true" class="p-fluid">
                    <div v-if="selectedOrder">
                        <h3>Order #{{ selectedOrder.id }}</h3>
                        <p><strong>Client:</strong> {{ selectedOrder.client ? `${selectedOrder.client.prenom} ${selectedOrder.client.nom}` : 'N/A' }}</p>
                        <p><strong>Status:</strong> {{ selectedOrder.statut }}</p>
                        <p><strong>Order Date:</strong> {{ formatDate(selectedOrder.date_commande) }}</p>
                        <p><strong>Total Amount:</strong> {{ selectedOrder.montant_total }}</p>
                        <h4>Products:</h4>
                        <ul>
                            <li v-for="product in selectedOrder.products" :key="product.id">
                                {{ product.nom }} - Quantity: {{ product.pivot.quantite }} - Unit Price: {{ product.pivot.prix_unitaire }}
                            </li>
                        </ul>
                        <div class="flex justify-content-between mt-3">
                            <Button
                                v-if="selectedOrder.detail_order && selectedOrder.detail_order.delivery_note_path"
                                label="View Delivery Note"
                                icon="pi pi-file-pdf"
                                class="p-button-secondary"
                                @click="viewFile(selectedOrder.detail_order.delivery_note_path)"
                            />
                            <Button
                                v-if="selectedOrder.detail_order && selectedOrder.detail_order.invoice_path"
                                label="View Invoice"
                                icon="pi pi-file-pdf"
                                class="p-button-secondary"
                                @click="viewFile(selectedOrder.detail_order.invoice_path)"
                            />
                        </div>
                    </div>
                    <template #footer>
                        <Button
                            v-if="selectedOrder && selectedOrder.statut === 'validé'"
                            label="Mark as Delivered"
                            icon="pi pi-check"
                            class="p-button-success mr-2"
                            @click="markAsDelivered"
                        />
                        <Button label="Close" icon="pi pi-times" class="p-button-text" @click="orderDetailsDialog = false" />
                    </template>
                </Dialog>

            </div>
        </div>
    </div>
</template>
