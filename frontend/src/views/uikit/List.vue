<script setup>
import { ref, onMounted, computed } from 'vue';
import { useStore } from 'vuex';
import { useToast } from 'primevue/usetoast';
import axios from 'axios';
import InputNumber from 'primevue/inputnumber';

const store = useStore();
const toast = useToast();

const orderDialogVisible = ref(false);
const selectedProduct = ref(null);
const orderDetails = ref({
    prenom: '',
    nom: '',
    email: '',
    numero_telephone: '',
    adresse: '',
    sexe: '',
    products: []
});

const user = computed(() => store.state.user.currentUser);

const totalPrice = computed(() => {
    if (selectedProduct.value && orderDetails.value.products[0]) {
        return selectedProduct.value.prix * orderDetails.value.products[0].quantite;
    }
    return 0;
});

const updateQuantity = (quantity) => {
    if (orderDetails.value.products[0]) {
        orderDetails.value.products[0].quantite = quantity;
    }
};

const setCookie = (name, value, days) => {
    const date = new Date();
    date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
    const expires = "expires=" + date.toUTCString();
    document.cookie = name + "=" + JSON.stringify(value) + ";" + expires + ";path=/";
};

const getCookie = (name) => {
    const nameEQ = name + "=";
    const ca = document.cookie.split(';');
    for(let i = 0; i < ca.length; i++) {
        let c = ca[i];
        while (c.charAt(0) === ' ') c = c.substring(1, c.length);
        if (c.indexOf(nameEQ) === 0) return JSON.parse(c.substring(nameEQ.length, c.length));
    }
    return null;
};

const openOrderDialog = (product) => {
    selectedProduct.value = product;

    // Récupération des informations du client depuis le store
    const storeClient = store.getters['user/getClient'];
    const storeUser = store.getters['user/getUser'];

    // Récupération des informations du client depuis le cookie
    const savedClientInfo = getCookie('clientInfo');

    // Crée l'objet client en priorisant les données du store, puis du cookie
    const clientInfo = {
        prenom: storeClient?.prenom || savedClientInfo?.prenom || '',
        nom: storeClient?.nom || savedClientInfo?.nom || '',
        email: storeUser?.email || savedClientInfo?.email || '',
        numero_telephone: storeClient?.numero_telephone || savedClientInfo?.numero_telephone || '',
        adresse: storeClient?.adresse || savedClientInfo?.adresse || '',
        sexe: storeClient?.sexe || savedClientInfo?.sexe || ''
    };

    orderDetails.value = {
        ...clientInfo,
        products: [{
            product_id: product.id,
            quantite: 1,
            prix_unitaire: product.prix
        }]
    };

    orderDialogVisible.value = true;
};

const closeOrderDialog = () => {
    orderDialogVisible.value = false;
    selectedProduct.value = null;
};

const placeOrder = async () => {
    try {
        const response = await axios.post('http://127.0.0.1:8000/api/orders', orderDetails.value);
        toast.add({ severity: 'success', summary: 'Commande passée', detail: 'Votre commande a été passée avec succès', life: 3000 });

        // Sauvegarde des informations du client dans un cookie
        const clientInfo = {
            prenom: orderDetails.value.prenom,
            nom: orderDetails.value.nom,
            email: orderDetails.value.email,
            numero_telephone: orderDetails.value.numero_telephone,
            adresse: orderDetails.value.adresse,
            sexe: orderDetails.value.sexe
        };
        setCookie('clientInfo', clientInfo, 30);

        closeOrderDialog();
    } catch (error) {
        console.error('Erreur lors de la commande:', error);
        toast.add({ severity: 'error', summary: 'Erreur', detail: 'Impossible de passer la commande', life: 3000 });
    }
};

const addToWishlist = (product) => {
    const wishlist = JSON.parse(localStorage.getItem('wishlist') || '[]');
    const existingProduct = wishlist.find(item => item.id === product.id);

    if (!existingProduct) {
        wishlist.push(product);
        localStorage.setItem('wishlist', JSON.stringify(wishlist));
        toast.add({ severity: 'success', summary: 'Ajouté', detail: 'Produit ajouté à la liste de souhaits', life: 3000 });
    } else {
        toast.add({ severity: 'info', summary: 'Déjà ajouté', detail: 'Ce produit est déjà dans votre liste de souhaits', life: 3000 });
    }
};

const dataviewValue = ref([]);
const layout = ref('grid');
const sortKey = ref(null);
const sortOrder = ref(null);
const sortField = ref(null);
const sortOptions = ref([
    { label: 'Price High to Low', value: '!prix' },
    { label: 'Price Low to High', value: 'prix' }
]);

const fetchProducts = async () => {
    try {
        const response = await axios.get('http://127.0.0.1:8000/api/products');
        const products = response.data;

        // Fetch category names for each product
        const fetchCategoryNames = products.map(async (product) => {
            const categoryResponse = await axios.get(`http://127.0.0.1:8000/api/categories/${product.category_id}`);
            product.categoryName = categoryResponse.data.name;
            return product;
        });

        dataviewValue.value = await Promise.all(fetchCategoryNames);
    } catch (error) {
        console.error('Error fetching products:', error);
    }
};

onMounted(() => {
    fetchProducts();
});

const onSortChange = (event) => {
    const value = event.value.value;
    const sortValue = event.value;

    if (value.indexOf('!') === 0) {
        sortOrder.value = -1;
        sortField.value = value.substring(1, value.length);
        sortKey.value = sortValue;
    } else {
        sortOrder.value = 1;
        sortField.value = value;
        sortKey.value = sortValue;
    }
};

const getSeverity = (product) => {
    if (product.quantite_en_stock > 10) {
        return 'success';
    } else if (product.quantite_en_stock <= 10 && product.quantite_en_stock > 0) {
        return 'warning';
    } else {
        return 'danger';
    }
};
</script>

<template>
    <div class="grid">
        <div class="col-12">
            <div class="card">
                <h5>DataView</h5>
                <DataView :value="dataviewValue" :layout="layout" :paginator="true" :rows="9" :sortOrder="sortOrder" :sortField="sortField">
                    <template #header>
                        <div class="grid grid-nogutter">
                            <div class="col-6 text-left">
                                <Dropdown v-model="sortKey" :options="sortOptions" optionLabel="label" placeholder="Sort By Price" @change="onSortChange($event)" />
                            </div>
                            <div class="col-6 text-right">
                                <DataViewLayoutOptions v-model="layout" />
                            </div>
                        </div>
                    </template>

                    <template #list="slotProps">
                        <div class="grid grid-nogutter">
                            <div v-for="(item, index) in slotProps.items" :key="index" class="col-12">
                                <div class="flex flex-column sm:flex-row sm:align-items-center p-4 gap-3" :class="{ 'border-top-1 surface-border': index !== 0 }">
                                    <div class="surface-50 flex justify-content-center border-round p-3">
                                        <div class="relative mx-auto">
                                            <img class="border-round fixed-image" :src="`http://127.0.0.1:8000/storage/${item.photo}`" :alt="item.nom" />
                                            <Tag :value="item.inventoryStatus" :severity="getSeverity(item)" class="absolute" style="left: 4px; top: 4px"></Tag>
                                        </div>
                                    </div>
                                    <div class="flex flex-column md:flex-row justify-content-between md:align-items-center flex-1 gap-4">
                                        <div class="flex flex-row md:flex-column justify-content-between align-items-start gap-2">
                                            <div>
                                                <span class="font-medium text-secondary text-sm">{{ item.categoryName }}</span>
                                                <div class="text-lg font-medium text-900 mt-2">{{ item.nom }}</div>
                                            </div>
                                            <div class="surface-100 p-1" style="border-radius: 30px">
                                                <div class="surface-0 flex align-items-center gap-2 justify-content-center py-1 px-2" style="border-radius: 30px; box-shadow: 0px 1px 2px 0px rgba(0, 0, 0, 0.04), 0px 1px 2px 0px rgba(0, 0, 0, 0.06)">
                                                    <span class="text-900 font-medium text-sm">{{ item.rating }}</span>
                                                    <i class="pi pi-star-fill text-yellow-500"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex flex-column md:align-items-end gap-5">
                                            <span class="text-xl font-semibold text-900">{{ item.prix }}</span>
                                            <div class="flex flex-row-reverse md:flex-row gap-2">
                                                <Button icon="pi pi-shopping-cart" outlined></Button>
                                                <Button icon="pi pi-shopping-cart" label="Buy Now" :disabled="item.quantite_en_stock === 0" class="flex-auto md:flex-initial white-space-nowrap"></Button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>

                    <template #grid="slotProps">
                        <div class="grid grid-nogutter">
                            <div v-for="(item, index) in slotProps.items" :key="index" class="col-12 sm:col-6 md:col-4 p-2">
                                <div class="p-4 border-1 surface-border surface-card border-round flex flex-column">
                                    <div class="surface-50 flex justify-content-center border-round p-3">
                                        <div class="relative mx-auto">
                                            <img class="border-round w-full" :src="`http://127.0.0.1:8000/storage/${item.photo}`" :alt="item.nom" style="max-width: 200px" />
                                            <Tag :value="item.inventoryStatus" :severity="getSeverity(item)" class="absolute" style="left: 4px; top: 4px"></Tag>
                                        </div>
                                    </div>
                                    <div class="pt-4">
                                        <div class="flex flex-row justify-content-between align-items-start gap-2">
                                            <div>
                                                <span class="font-medium text-secondary text-sm">{{ item.categoryName }}</span>
                                                <div class="text-lg font-medium text-900 mt-1">{{ item.nom }}</div>
                                            </div>
                                            <div class="surface-100 p-1" style="border-radius: 30px">
                                                <div class="surface-0 flex align-items-center gap-2 justify-content-center py-1 px-2" style="border-radius: 30px; box-shadow: 0px 1px 2px 0px rgba(0, 0, 0, 0.04), 0px 1px 2px 0px rgba(0, 0, 0, 0.06)">
                                                    <span class="text-900 font-medium text-sm">{{ item.rating }}</span>
                                                    <i class="pi pi-star-fill text-yellow-500"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex flex-column gap-4 mt-4">
                                            <span class="text-2xl font-semibold text-900">{{ item.prix }}</span>
                                            <div class="flex gap-2">
                                                <Button icon="pi pi-shopping-cart" label="Buy Now" :disabled="item.quantite_en_stock === 0" class="flex-auto white-space-nowrap" @click="openOrderDialog(item)"></Button>
                                                <Button icon="pi pi-heart" outlined @click="addToWishlist(item)"></Button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </DataView>
            </div>
        </div>
    </div>
    <Dialog v-model:visible="orderDialogVisible" header="Order Details" :style="{width: '50vw'}" :modal="true">
        <div class="p-fluid">
            <div class="field">
                <label for="prenom">Prénom</label>
                <InputText id="prenom" v-model="orderDetails.prenom" required />
            </div>
            <div class="field">
                <label for="nom">Nom</label>
                <InputText id="nom" v-model="orderDetails.nom" required />
            </div>
            <div class="field">
                <label for="email">Email</label>
                <InputText id="email" v-model="orderDetails.email" required type="email" />
            </div>
            <div class="field">
                <label for="numero_telephone">Numéro de téléphone</label>
                <InputText id="numero_telephone" v-model="orderDetails.numero_telephone" required />
            </div>
            <div class="field">
                <label for="adresse">Adresse</label>
                <InputText id="adresse" v-model="orderDetails.adresse" required />
            </div>
            <div class="field">
                <label for="sexe">Sexe</label>
                <Dropdown id="sexe" v-model="orderDetails.sexe" :options="['M', 'F']" placeholder="Sélectionnez le sexe" />
            </div>
            <div class="field">
                <label for="quantity">Quantité</label>
                <InputNumber id="quantity" v-model="orderDetails.products[0].quantite" @input="updateQuantity" :min="1" required />
            </div>
            <div class="field">
                <label>Prix total</label>
                <div class="text-2xl font-bold">{{ totalPrice.toFixed(2) }} €</div>
            </div>
        </div>
        <template #footer>
            <Button label="Annuler" icon="pi pi-times" @click="closeOrderDialog" class="p-button-text"/>
            <Button label="Commander" icon="pi pi-check" @click="placeOrder" autofocus />
        </template>
    </Dialog>
</template>

<style scoped>
.fixed-image {
    width: 300px;
    height: 300px;
    object-fit: cover;
}
</style>

