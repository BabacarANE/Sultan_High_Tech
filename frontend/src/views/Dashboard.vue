<script setup>
import { onMounted, ref, computed } from 'vue';
import { useStore } from 'vuex';
import axios from 'axios';
import Chart from 'primevue/chart';

const store = useStore();
const dashboardStats = ref(null);

const fetchDashboardStats = async () => {
    try {
        const response = await axios.get('http://127.0.0.1:8000/api/dashboard/stats');
        dashboardStats.value = response.data;
    } catch (error) {
        console.error('Erreur lors de la récupération des statistiques:', error);
    }
};

onMounted(() => {
    fetchDashboardStats();
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'XOF' }).format(value);
};

const clientsByGenderChartData = computed(() => {
    if (!dashboardStats.value) return null;

    const maleCount = dashboardStats.value.clientsByGender.find(item => item.sexe === 'M')?.count || 0;
    const femaleCount = dashboardStats.value.clientsByGender.find(item => item.sexe === 'F')?.count || 0;

    return {
        labels: ['Hommes', 'Femmes'],
        datasets: [
            {
                data: [maleCount, femaleCount],
                backgroundColor: ['#42A5F5', '#FFA726'],
                hoverBackgroundColor: ['#64B5F6', '#FFB74D']
            }
        ]
    };
});

const productsByCategoryChartData = computed(() => {
    if (!dashboardStats.value) return null;

    const categories = dashboardStats.value.productsByCategory.map(item => `Catégorie ${item.category_id}`);
    const counts = dashboardStats.value.productsByCategory.map(item => item.count);

    return {
        labels: categories,
        datasets: [
            {
                label: 'Nombre de produits',
                data: counts,
                backgroundColor: '#4CAF50',
                borderColor: '#45a049',
                borderWidth: 1
            }
        ]
    };
});

const pieChartOptions = {
    plugins: {
        legend: {
            labels: {
                usePointStyle: true
            }
        }
    }
};

const barChartOptions = {
    scales: {
        y: {
            beginAtZero: true,
            title: {
                display: true,
                text: 'Nombre de produits'
            }
        },
        x: {
            title: {
                display: true,
                text: 'Catégories'
            }
        }
    },
    plugins: {
        legend: {
            display: false
        }
    }
};
</script>

<template>
    <div class="grid" v-if="dashboardStats">
        <!-- Cartes existantes -->
        <div class="col-12 lg:col-6 xl:col-3">
            <div class="card mb-0">
                <div class="flex justify-content-between mb-3">
                    <div>
                        <span class="block text-500 font-medium mb-3">Clients</span>
                        <div class="text-900 font-medium text-xl">{{ dashboardStats.totalClients }}</div>
                    </div>
                    <div class="flex align-items-center justify-content-center bg-blue-100 border-round" style="width: 2.5rem; height: 2.5rem">
                        <i class="pi pi-users text-blue-500 text-xl"></i>
                    </div>
                </div>
                <span class="text-500">Total des clients</span>
            </div>
        </div>
        <div class="col-12 lg:col-6 xl:col-3">
            <div class="card mb-0">
                <div class="flex justify-content-between mb-3">
                    <div>
                        <span class="block text-500 font-medium mb-3">Produits</span>
                        <div class="text-900 font-medium text-xl">{{ dashboardStats.totalProducts }}</div>
                    </div>
                    <div class="flex align-items-center justify-content-center bg-orange-100 border-round" style="width: 2.5rem; height: 2.5rem">
                        <i class="pi pi-shopping-cart text-orange-500 text-xl"></i>
                    </div>
                </div>
                <span class="text-500">Total des produits</span>
            </div>
        </div>
        <div class="col-12 lg:col-6 xl:col-3">
            <div class="card mb-0">
                <div class="flex justify-content-between mb-3">
                    <div>
                        <span class="block text-500 font-medium mb-3">Chiffre d'affaires</span>
                        <div class="text-900 font-medium text-xl">{{ formatCurrency(dashboardStats.totalAmountDeliveredOrders) }}</div>
                    </div>
                    <div class="flex align-items-center justify-content-center bg-cyan-100 border-round" style="width: 2.5rem; height: 2.5rem">
                        <i class="pi pi-money-bill text-cyan-500 text-xl"></i>
                    </div>
                </div>
                <span class="text-500">Total des commandes livrées</span>
            </div>
        </div>
        <div class="col-12 lg:col-6 xl:col-3">
            <div class="card mb-0">
                <div class="flex justify-content-between mb-3">
                    <div>
                        <span class="block text-500 font-medium mb-3">Valeur du stock</span>
                        <div class="text-900 font-medium text-xl">{{ formatCurrency(dashboardStats.totalStockValue) }}</div>
                    </div>
                    <div class="flex align-items-center justify-content-center bg-purple-100 border-round" style="width: 2.5rem; height: 2.5rem">
                        <i class="pi pi-box text-purple-500 text-xl"></i>
                    </div>
                </div>
                <span class="text-500">Valeur totale du stock</span>
            </div>
        </div>

        <!-- Diagramme circulaire pour la répartition des clients par sexe -->
        <div class="col-12 lg:col-6">
            <div class="card">
                <h5>Répartition des clients par sexe</h5>
                <Chart type="pie" :data="clientsByGenderChartData" :options="pieChartOptions" class="w-full md:w-30rem" />
            </div>
        </div>

        <!-- Nouveau diagramme en barre pour les produits par catégorie -->
        <div class="col-12 lg:col-6">
            <div class="card">
                <h5>Nombre de produits par catégorie</h5>
                <Chart type="bar" :data="productsByCategoryChartData" :options="barChartOptions" class="w-full" />
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Vous pouvez ajouter des styles spécifiques ici si nécessaire */
</style>
