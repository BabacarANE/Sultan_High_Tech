<script setup>
import { ref, computed, onMounted } from 'vue';
import { useStore } from 'vuex';
import AppMenuItem from './AppMenuItem.vue';

const store = useStore();

const isAdmin = computed(() => store.getters['user/isAdmin']);
const isUser = computed(() => store.getters['user/isUser']);
const isDeliveryService = computed(() => store.getters['user/isDeliveryService']);
const userRole = computed(() => store.getters['user/userRole']);
const currentUser = computed(() => store.getters['user/getUser']);

const model = computed(() => {
    console.log('Calculating menu model');
    console.log('Current user:', currentUser.value);
    console.log('User role:', userRole.value);
    console.log('Is Admin:', isAdmin.value);
    console.log('Is User:', isUser.value);
    console.log('Is Delivery Service:', isDeliveryService.value);

    const baseMenu = [
        {
            label: 'HOME',
            items: [
                {label: 'Produits', icon: 'pi pi-fw pi-list', to: '/'},
                {label: 'Panier', icon: 'pi pi-shopping-cart', to: '/pages/Panier', preventExact: true}
            ]
        }
    ];
    if (isUser.value){
        baseMenu.push({
            label: 'INFORMATIONS',
            icon: 'pi pi-fw pi-briefcase',
            to: '/pages',
            items: [
                {
                    label: 'History order',
                    icon: 'pi pi-fw pi-pencil',
                    to: '/pages/history'
                },
                {
                    label: 'Information',
                    icon: 'pi pi-fw pi-user',
                    to: '/pages/information'
                }

            ]
        });
    }

    if (isAdmin.value) {
        baseMenu.push({
            label: 'DASHBOARD',
            items: [{label: 'Dashboard', icon: 'pi pi-fw pi-home', to: '/dashboard'}]
        });
        baseMenu.push({
            label: 'ADMINISTRATION',
            icon: 'pi pi-fw pi-briefcase',
            to: '/pages',
            items: [
                {
                    label: 'Product',
                    icon: 'pi pi-fw pi-pencil',
                    to: '/pages/crud'
                },
                {
                    label: 'Category',
                    icon: 'pi pi-fw pi-pencil',
                    to: '/pages/Category'
                },
                {
                    label: 'Client',
                    icon: 'pi pi-fw pi-pencil',
                    to: '/pages/Client'
                },
                {
                    label: 'User',
                    icon: 'pi pi-fw pi-user',
                    to: '/pages/User'
                },
                {
                    label: 'Order',
                    icon: 'pi pi-fw pi-pencil',
                    to: '/pages/Order'
                },
                {
                    label: 'Delivery Service',
                    icon: 'pi pi-fw pi-pencil',
                    to: '/pages/Delivery'
                },
                {
                    label: 'Roles',
                    icon: 'pi pi-fw pi-pencil',
                    to: '/pages/Role'
                }
            ]
        });
    } else if (isDeliveryService.value) {
        baseMenu.push({
            label: 'GESTION',
            icon: 'pi pi-fw pi-briefcase',
            to: '/pages',
            items: [
                {
                    label: 'Delivery Service',
                    icon: 'pi pi-fw pi-pencil',
                    to: '/pages/Delivery'
                }
            ]
        });
    }

    return baseMenu;
});

onMounted(() => {
    console.log('Component mounted');
    console.log('Initial user state:', store.state.user);
});
</script>

<template>
    <ul class="layout-menu">
        <template v-for="(item, i) in model" :key="item">
            <app-menu-item v-if="!item.separator" :item="item" :index="i"></app-menu-item>
            <li v-if="item.separator" class="menu-separator"></li>
        </template>
    </ul>
</template>

<style lang="scss" scoped>
// Vos styles ici si nécessaire
</style>
