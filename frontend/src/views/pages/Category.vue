<script setup>
import { FilterMatchMode } from 'primevue/api';
import { ref, onMounted, onBeforeMount } from 'vue';
import { useToast } from 'primevue/usetoast';
import FileUpload from 'primevue/fileupload';
import axios from 'axios';

const toast = useToast();

const products = ref(null);
const productDialog = ref(false);
const deleteProductDialog = ref(false);
const deleteProductsDialog = ref(false);
const product = ref({});
const selectedProducts = ref(null);
const dt = ref(null);
const filters = ref({});
const submitted = ref(false);
const photoFile = ref(null);
const getBadgeSeverity = (quantiteEnStock) => {
    if (quantiteEnStock > 10) return 'success';
    if (quantiteEnStock > 0) return 'warning';
    return 'danger';
};

onBeforeMount(() => {
    initFilters();
});

onMounted(() => {
    fetchProducts();
});

const fetchProducts = async () => {
    try {
        const response = await axios.get('http://127.0.0.1:8000/api/products');
        products.value = response.data;
    } catch (error) {
        console.error('Error fetching products:', error);
        toast.add({ severity: 'error', summary: 'Error', detail: 'Failed to fetch products', life: 3000 });
    }
};

const formatCurrency = (value) => {
    return parseFloat(value).toLocaleString('fr-FR', { style: 'currency', currency: 'XOF' });
};

const openNew = () => {
    product.value = {};
    submitted.value = false;
    productDialog.value = true;
};

const hideDialog = () => {
    productDialog.value = false;
    submitted.value = false;
};

const onPhotoSelect = (event) => {
    photoFile.value = event.files[0];
};


const saveProduct = async () => {
    submitted.value = true;
    if (product.value.nom && product.value.nom.trim() && product.value.prix) {
        try {
            const formData = new FormData();
            formData.append('nom', product.value.nom);
            formData.append('description', product.value.description);
            formData.append('prix', product.value.prix);
            formData.append('quantite_en_stock', product.value.quantite_en_stock);
            formData.append('category_id', product.value.category_id);

            if (photoFile.value) {
                formData.append('photo', photoFile.value);
            }

            if (product.value.id) {
                // Update existing product
                await axios.post(`http://127.0.0.1:8000/api/products/${product.value.id}`, formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                });
                toast.add({ severity: 'success', summary: 'Successful', detail: 'Product Updated', life: 3000 });
            } else {
                // Create new product
                await axios.post('http://127.0.0.1:8000/api/products', formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                });
                toast.add({ severity: 'success', summary: 'Successful', detail: 'Product Created', life: 3000 });
            }
            productDialog.value = false;
            product.value = {};
            photoFile.value = null;
            await fetchProducts(); // Refresh the product list
        } catch (error) {
            console.error('Error saving product:', error);
            toast.add({ severity: 'error', summary: 'Error', detail: 'Failed to save product', life: 3000 });
        }
    }
};

const editProduct = (editProduct) => {
    product.value = {...editProduct};
    productDialog.value = true;
};

const confirmDeleteProduct = (editProduct) => {
    product.value = editProduct;
    deleteProductDialog.value = true;
};

const deleteProduct = async () => {
    try {
        await axios.delete(`http://127.0.0.1:8000/api/products/${product.value.id}`);
        products.value = products.value.filter((val) => val.id !== product.value.id);
        deleteProductDialog.value = false;
        product.value = {};
        toast.add({severity: 'success', summary: 'Successful', detail: 'Product Deleted', life: 3000});
    } catch (error) {
        console.error('Error deleting product:', error);
        toast.add({severity: 'error', summary: 'Error', detail: 'Failed to delete product', life: 3000});
    }
};

const exportCSV = () => {
    dt.value.exportCSV();
};

const confirmDeleteSelected = () => {
    deleteProductsDialog.value = true;
};

const deleteSelectedProducts = async () => {
    try {
        for (let product of selectedProducts.value) {
            await axios.delete(`http://127.0.0.1:8000/api/products/${product.id}`);
        }
        products.value = products.value.filter((val) => !selectedProducts.value.includes(val));
        deleteProductsDialog.value = false;
        selectedProducts.value = null;
        toast.add({severity: 'success', summary: 'Successful', detail: 'Products Deleted', life: 3000});
    } catch (error) {
        console.error('Error deleting selected products:', error);
        toast.add({severity: 'error', summary: 'Error', detail: 'Failed to delete selected products', life: 3000});
    }
};

const initFilters = () => {
    filters.value = {
        global: {value: null, matchMode: FilterMatchMode.CONTAINS}
    };
};
const categories = ref([]);

onMounted(() => {
    fetchCategories();
});

const fetchCategories = async () => {
    try {
        const response = await axios.get('http://127.0.0.1:8000/api/categories');
        categories.value = response.data;
    } catch (error) {
        console.error('Error fetching categories:', error);
        toast.add({ severity: 'error', summary: 'Error', detail: 'Failed to fetch categories', life: 3000 });
    }
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
                                    :disabled="!selectedProducts || !selectedProducts.length"/>
                        </div>
                    </template>

                    <template v-slot:end>
                        <FileUpload mode="basic" accept="image/*" :maxFileSize="1000000" label="Import"
                                    chooseLabel="Import" class="mr-2 inline-block"/>
                        <Button label="Export" icon="pi pi-upload" severity="help" @click="exportCSV($event)"/>
                    </template>
                </Toolbar>

                <DataTable
                    ref="dt"
                    :value="products"
                    v-model:selection="selectedProducts"
                    dataKey="id"
                    :paginator="true"
                    :rows="10"
                    :filters="filters"
                    paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                    :rowsPerPageOptions="[5, 10, 25]"
                    currentPageReportTemplate="Showing {first} to {last} of {totalRecords} products"
                >
                    <template #header>
                        <div class="flex flex-column md:flex-row md:justify-content-between md:align-items-center">
                            <h5 class="m-0">Manage Products</h5>
                            <IconField iconPosition="left" class="block mt-2 md:mt-0">
                                <InputIcon class="pi pi-search"/>
                                <InputText class="w-full sm:w-auto" v-model="filters['global'].value"
                                           placeholder="Search..."/>
                            </IconField>
                        </div>
                    </template>

                    <Column selectionMode="multiple" headerStyle="width: 3rem"></Column>
                    <Column field="id" header="ID" :sortable="true" headerStyle="width:14%; min-width:10rem;">
                        <template #body="slotProps">
                            <span class="p-column-title">ID</span>
                            {{ slotProps.data.id }}
                        </template>
                    </Column>
                    <Column field="nom" header="Name" :sortable="true" headerStyle="width:14%; min-width:10rem;">
                        <template #body="slotProps">
                            <span class="p-column-title">Name</span>
                            {{ slotProps.data.nom }}
                        </template>
                    </Column>
                    <Column header="Image" headerStyle="width:14%; min-width:10rem;">
                        <template #body="slotProps">
                            <span class="p-column-title">Image</span>
                            <img :src="`http://127.0.0.1:8000/storage/${slotProps.data.photo}`" :alt="slotProps.data.nom"
                                 class="shadow-2" width="100"/>
                        </template>
                    </Column>
                    <Column field="prix" header="Price" :sortable="true" headerStyle="width:14%; min-width:8rem;">
                        <template #body="slotProps">
                            <span class="p-column-title">Price</span>
                            {{ formatCurrency(slotProps.data.prix) }}
                        </template>
                    </Column>
                    <Column field="category_id" header="Category" :sortable="true"
                            headerStyle="width:14%; min-width:10rem;">
                        <template #body="slotProps">
                            <span class="p-column-title">Category</span>
                            {{ slotProps.data.category_id }}
                        </template>
                    </Column>
                    <Column field="quantite_en_stock" header="Stock" :sortable="true"
                            headerStyle="width:14%; min-width:10rem;">
                        <template #body="slotProps">
                            <span class="p-column-title">Stock</span>
                            <Tag :severity="getBadgeSeverity(slotProps.data.quantite_en_stock)">
                                {{ slotProps.data.quantite_en_stock }}
                            </Tag>
                        </template>
                    </Column>
                    <Column headerStyle="min-width:10rem;">
                        <template #body="slotProps">
                            <Button icon="pi pi-pencil" class="mr-2" severity="success" rounded
                                    @click="editProduct(slotProps.data)"/>
                            <Button icon="pi pi-trash" class="mt-2" severity="warning" rounded
                                    @click="confirmDeleteProduct(slotProps.data)"/>
                        </template>
                    </Column>
                </DataTable>

                <Dialog v-model:visible="productDialog" :style="{ width: '450px' }" header="Product Details"
                        :modal="true" class="p-fluid">
                    <img :src="`http://127.0.0.1:8000/storage/${product.photo}`" :alt="product.nom" v-if="product.photo"
                         width="150" class="mt-0 mx-auto mb-5 block shadow-2"/>
                    <div class="field">
                        <label for="nom">Name</label>
                        <InputText id="nom" v-model.trim="product.nom" required="true" autofocus
                                   :invalid="submitted && !product.nom"/>
                        <small class="p-invalid" v-if="submitted && !product.nom">Name is required.</small>
                    </div>
                    <div class="field">
                        <label for="description">Description</label>
                        <Textarea id="description" v-model="product.description" required="true" rows="3" cols="20"/>
                    </div>

                    <div class="formgrid grid">
                        <div class="field col">
                            <label for="prix">Price</label>
                            <InputNumber id="prix" v-model="product.prix" mode="currency" currency="XOF" locale="fr-FR"
                                         :invalid="submitted && !product.prix" :required="true"/>
                            <small class="p-invalid" v-if="submitted && !product.prix">Price is required.</small>
                        </div>
                        <div class="field col">
                            <label for="quantite_en_stock">Quantity</label>
                            <InputNumber id="quantite_en_stock" v-model="product.quantite_en_stock" integeronly/>
                        </div>
                    </div>
                    <div class="field">
                        <label for="category_id">Category</label>
                        <Dropdown id="category_id" v-model="product.category_id" :options="categories"
                                  optionLabel="name" optionValue="id" placeholder="Select a category">
                            <template #option="slotProps">
                                {{ slotProps.option.name }}
                            </template>
                        </Dropdown>
                    </div>
                    <div class="field">
                        <label for="photo">Photo</label>
                        <FileUpload name="photo" url="./upload" @select="onPhotoSelect" :auto="true"
                                    accept="image/*" :maxFileSize="1000000">
                            <template #empty>
                                <p>Drag and drop image here to upload.</p>
                            </template>
                        </FileUpload>
                    </div>
                    <template #footer>
                        <Button label="Cancel" icon="pi pi-times" text @click="hideDialog"/>
                        <Button label="Save" icon="pi pi-check" text @click="saveProduct"/>
                    </template>
                </Dialog>

                <Dialog v-model:visible="deleteProductDialog" :style="{ width: '450px' }" header="Confirm"
                        :modal="true">
                    <div class="flex align-items-center justify-content-center">
                        <i class="pi pi-exclamation-triangle mr-3" style="font-size: 2rem"/>
                        <span v-if="product">Are you sure you want to delete <b>{{ product.nom }}</b>?</span>
                    </div>
                    <template #footer>
                        <Button label="No" icon="pi pi-times" text @click="deleteProductDialog = false"/>
                        <Button label="Yes" icon="pi pi-check" text @click="deleteProduct"/>
                    </template>
                </Dialog>

                <Dialog v-model:visible="deleteProductsDialog" :style="{ width: '450px' }" header="Confirm"
                        :modal="true">
                    <div class="flex align-items-center justify-content-center">
                        <i class="pi pi-exclamation-triangle mr-3" style="font-size: 2rem"/>
                        <span v-if="product">Are you sure you want to delete the selected products?</span>
                    </div>
                    <template #footer>
                        <Button label="No" icon="pi pi-times" text @click="deleteProductsDialog = false"/>
                        <Button label="Yes" icon="pi pi-check" text @click="deleteSelectedProducts"/>
                    </template>
                </Dialog>
            </div>
        </div>
    </div>
</template>
