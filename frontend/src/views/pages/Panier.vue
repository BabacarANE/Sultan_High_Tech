<script setup>
import { ref, onMounted, computed } from 'vue';
import { useStore } from 'vuex';
import { useToast } from 'primevue/usetoast';
import axios from 'axios';
import InputNumber from 'primevue/inputnumber';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';

const store = useStore();
const toast = useToast();

const wishlistItems = ref([]);
const showOrderDialog = ref(false);
const clientInfo = ref({});

const user = computed(() => store.state.user.currentUser);

const totalPrice = computed(() => {
    return wishlistItems.value.reduce((total, product) => {
        return total + (Number(product.prix) * product.quantite);
    }, 0);
});

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

const loadWishlist = () => {
    const wishlist = JSON.parse(localStorage.getItem('wishlist') || '[]');
    wishlistItems.value = wishlist.map(item => ({
        ...item,
        quantite: 1,
        prix: Number(item.prix)
    }));
};

const removeFromWishlist = (product) => {
    const index = wishlistItems.value.findIndex(item => item.id === product.id);
    if (index !== -1) {
        wishlistItems.value.splice(index, 1);
        localStorage.setItem('wishlist', JSON.stringify(wishlistItems.value));
        toast.add({ severity: 'success', summary: 'Supprimé', detail: 'Produit retiré de la liste de souhaits', life: 3000 });
    }
};

const openOrderDialog = () => {
    clientInfo.value = getCookie('clientInfo') || user.value || {};
    showOrderDialog.value = true;
};

const placeOrder = async () => {
    if (!clientInfo.value.nom || !clientInfo.value.prenom || !clientInfo.value.email || !clientInfo.value.adresse) {
        toast.add({ severity: 'error', summary: 'Erreur', detail: 'Veuillez remplir tous les champs obligatoires', life: 3000 });
        return;
    }

    const orderDetails = {
        ...clientInfo.value,
        products: wishlistItems.value.map(item => ({
            product_id: item.id,
            quantite: item.quantite,
            prix_unitaire: item.prix
        }))
    };

    try {
        const response = await axios.post('http://127.0.0.1:8000/api/orders', orderDetails);
        toast.add({ severity: 'success', summary: 'Commande passée', detail: 'Votre commande a été passée avec succès', life: 3000 });

        localStorage.setItem('wishlist', '[]');
        wishlistItems.value = [];
        showOrderDialog.value = false;
    } catch (error) {
        console.error('Erreur lors de la commande:', error);
        toast.add({ severity: 'error', summary: 'Erreur', detail: 'Impossible de passer la commande', life: 3000 });
    }
};

const updateQuantity = (product, newQuantity) => {
    product.quantite = Math.max(1, parseInt(newQuantity) || 1);
};

const formatPrice = (price) => {
    return typeof price === 'number' && !isNaN(price) ? price.toFixed(2) : '0.00';
};

onMounted(() => {
    loadWishlist();
});
</script>

<template>
    <div class="grid">
        <div class="col-12">
            <div class="card">
                <h5>Ma liste de souhaits</h5>
                <DataTable :value="wishlistItems" responsiveLayout="scroll">
                    <Column field="nom" header="Nom du produit"></Column>
                    <Column field="prix" header="Prix">
                        <template #body="slotProps">
                            {{ formatPrice(slotProps.data.prix) }} FCFA
                        </template>
                    </Column>
                    <Column header="Quantité">
                        <template #body="slotProps">
                            <InputNumber
                                v-model="slotProps.data.quantite"
                                @update:modelValue="updateQuantity(slotProps.data, $event)"
                                :min="1"
                                showButtons
                                buttonLayout="horizontal"
                                incrementButtonIcon="pi pi-plus"
                                decrementButtonIcon="pi pi-minus"
                                :step="1"
                            />
                        </template>
                    </Column>
                    <Column header="Total">
                        <template #body="slotProps">
                            {{ formatPrice(Number(slotProps.data.prix) * slotProps.data.quantite) }} FCFA
                        </template>
                    </Column>
                    <Column header="Actions">
                        <template #body="slotProps">
                            <Button icon="pi pi-trash" class="p-button-danger" @click="removeFromWishlist(slotProps.data)" />
                        </template>
                    </Column>
                </DataTable>
                <div class="flex justify-content-between align-items-center mt-4">
                    <div class="text-2xl font-bold">
                        Total: {{ formatPrice(totalPrice) }} FCFA
                    </div>
                    <Button label="Commander" icon="pi pi-shopping-cart" @click="openOrderDialog" :disabled="wishlistItems.length === 0" />
                </div>
            </div>
        </div>
    </div>

    <Dialog v-model:visible="showOrderDialog" modal header="Confirmation de commande" :style="{ width: '50vw' }">
        <div class="p-fluid">
            <div class="p-field">
                <label for="prenom">Prénom</label>
                <InputText id="prenom" v-model="clientInfo.prenom" required />
            </div>
            <div class="p-field">
                <label for="nom">Nom</label>
                <InputText id="nom" v-model="clientInfo.nom" required />
            </div>
            <div class="p-field">
                <label for="email">Email</label>
                <InputText id="email" v-model="clientInfo.email" required type="email" />
            </div>
            <div class="p-field">
                <label for="adresse">Adresse</label>
                <Textarea id="adresse" v-model="clientInfo.adresse" required rows="3" />
            </div>
            <div class="p-field">
                <label for="telephone">Téléphone</label>
                <InputText id="telephone" v-model="clientInfo.numero_telephone" />
            </div>
        </div>
        <template #footer>
            <Button label="Annuler" icon="pi pi-times" @click="showOrderDialog = false" class="p-button-text" />
            <Button label="Confirmer la commande" icon="pi pi-check" @click="placeOrder" autofocus />
        </template>
    </Dialog>
</template>

<style scoped>
.p-field {
    margin-bottom: 1rem;
}
</style>
