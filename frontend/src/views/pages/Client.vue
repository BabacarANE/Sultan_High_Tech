<script setup>
import { FilterMatchMode } from 'primevue/api';
import { ref, onMounted, onBeforeMount } from 'vue';
import { useToast } from 'primevue/usetoast';
import axios from 'axios';

const toast = useToast();

const categories = ref(null);
const categoryDialog = ref(false);
const deleteCategoryDialog = ref(false);
const deleteCategoriesDialog = ref(false);
const category = ref({});
const selectedCategories = ref(null);
const dt = ref(null);
const filters = ref({});
const submitted = ref(false);

onBeforeMount(() => {
    initFilters();
});

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

const formatDate = (value) => {
    return new Date(value).toLocaleString();
};

const openNew = () => {
    category.value = {};
    submitted.value = false;
    categoryDialog.value = true;
};

const hideDialog = () => {
    categoryDialog.value = false;
    submitted.value = false;
};

const saveCategory = async () => {
    submitted.value = true;
    if (category.value.name && category.value.name.trim()) {
        try {
            if (category.value.id) {
                // Update existing category
                await axios.post(`http://127.0.0.1:8000/api/categories/${category.value.id}`, category.value);
                toast.add({ severity: 'success', summary: 'Successful', detail: 'Category Updated', life: 3000 });
            } else {
                // Create new category
                await axios.post('http://127.0.0.1:8000/api/categories', category.value);
                toast.add({ severity: 'success', summary: 'Successful', detail: 'Category Created', life: 3000 });
            }
            categoryDialog.value = false;
            category.value = {};
            await fetchCategories(); // Refresh the category list
        } catch (error) {
            console.error('Error saving category:', error);
            toast.add({ severity: 'error', summary: 'Error', detail: 'Failed to save category', life: 3000 });
        }
    }
};

const editCategory = (editCategory) => {
    category.value = { ...editCategory };
    categoryDialog.value = true;
};

const confirmDeleteCategory = (editCategory) => {
    category.value = editCategory;
    deleteCategoryDialog.value = true;
};

const deleteCategory = async () => {
    try {
        await axios.delete(`http://127.0.0.1:8000/api/categories/${category.value.id}`);
        categories.value = categories.value.filter((val) => val.id !== category.value.id);
        deleteCategoryDialog.value = false;
        category.value = {};
        toast.add({ severity: 'success', summary: 'Successful', detail: 'Category Deleted', life: 3000 });
    } catch (error) {
        console.error('Error deleting category:', error);
        toast.add({ severity: 'error', summary: 'Error', detail: 'Failed to delete category', life: 3000 });
    }
};

const exportCSV = () => {
    dt.value.exportCSV();
};

const confirmDeleteSelected = () => {
    deleteCategoriesDialog.value = true;
};

const deleteSelectedCategories = async () => {
    try {
        for (let category of selectedCategories.value) {
            await axios.delete(`http://127.0.0.1:8000/api/categories/${category.id}`);
        }
        categories.value = categories.value.filter((val) => !selectedCategories.value.includes(val));
        deleteCategoriesDialog.value = false;
        selectedCategories.value = null;
        toast.add({ severity: 'success', summary: 'Successful', detail: 'Categories Deleted', life: 3000 });
    } catch (error) {
        console.error('Error deleting selected categories:', error);
        toast.add({ severity: 'error', summary: 'Error', detail: 'Failed to delete selected categories', life: 3000 });
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
                                    :disabled="!selectedCategories || !selectedCategories.length" />
                        </div>
                    </template>

                    <template v-slot:end>
                        <Button label="Export" icon="pi pi-upload" severity="help" @click="exportCSV($event)" />
                    </template>
                </Toolbar>

                <DataTable ref="dt" :value="categories" v-model:selection="selectedCategories" dataKey="id"
                           :paginator="true" :rows="10" :filters="filters"
                           paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                           :rowsPerPageOptions="[5, 10, 25]"
                           currentPageReportTemplate="Showing {first} to {last} of {totalRecords} categories">
                    <template #header>
                        <div class="flex flex-column md:flex-row md:justify-content-between md:align-items-center">
                            <h5 class="m-0">Manage Categories</h5>
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
                            <Button icon="pi pi-pencil" class="p-button-rounded p-button-success mr-2" @click="editCategory(slotProps.data)" />
                            <Button icon="pi pi-trash" class="p-button-rounded p-button-warning" @click="confirmDeleteCategory(slotProps.data)" />
                        </template>
                    </Column>
                </DataTable>

                <Dialog v-model:visible="categoryDialog" :style="{width: '450px'}" header="Category Details" :modal="true" class="p-fluid">
                    <div class="field">
                        <label for="name">Name</label>
                        <InputText id="name" v-model.trim="category.name" required="true" autofocus :class="{'p-invalid': submitted && !category.name}" />
                        <small class="p-invalid" v-if="submitted && !category.name">Name is required.</small>
                    </div>
                    <template #footer>
                        <Button label="Cancel" icon="pi pi-times" class="p-button-text" @click="hideDialog" />
                        <Button label="Save" icon="pi pi-check" class="p-button-text" @click="saveCategory" />
                    </template>
                </Dialog>

                <Dialog v-model:visible="deleteCategoryDialog" :style="{width: '450px'}" header="Confirm" :modal="true">
                    <div class="flex align-items-center justify-content-center">
                        <i class="pi pi-exclamation-triangle mr-3" style="font-size: 2rem" />
                        <span v-if="category">Are you sure you want to delete <b>{{category.name}}</b>?</span>
                    </div>
                    <template #footer>
                        <Button label="No" icon="pi pi-times" class="p-button-text" @click="deleteCategoryDialog = false" />
                        <Button label="Yes" icon="pi pi-check" class="p-button-text" @click="deleteCategory" />
                    </template>
                </Dialog>

                <Dialog v-model:visible="deleteCategoriesDialog" :style="{width: '450px'}" header="Confirm" :modal="true">
                    <div class="flex align-items-center justify-content-center">
                        <i class="pi pi-exclamation-triangle mr-3" style="font-size: 2rem" />
                        <span v-if="category">Are you sure you want to delete the selected categories?</span>
                    </div>
                    <template #footer>
                        <Button label="No" icon="pi pi-times" class="p-button-text" @click="deleteCategoriesDialog = false" />
                        <Button label="Yes" icon="pi pi-check" class="p-button-text" @click="deleteSelectedCategories" />
                    </template>
                </Dialog>
            </div>
        </div>
    </div>
</template>
