<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { useLayout } from '@/layout/composables/layout';
import { useRouter } from 'vue-router';
import { useStore } from 'vuex';
import axios from 'axios';

const { layoutConfig, onMenuToggle } = useLayout();

const outsideClickListener = ref(null);
const topbarMenuActive = ref(false);
const store = useStore();
const router = useRouter();
const showMenu = ref(false);

const isLoggedIn = computed(() => !!store.getters['user/getUser']);

const logout = async () => {
    try {
        // Récupérer le token depuis le store ou le localStorage
        const token = store.getters['user/getToken'] || localStorage.getItem('token');

        // Inclure le token dans les headers de la requête
        await axios.post('http://127.0.0.1:8000/api/logout', {}, {
            headers: {
                'Authorization': `Bearer ${token}`
            }
        });
        store.dispatch('user/logout');
        router.replace('/auth/login');
    } catch (error) {
        console.error('Logout failed', error);
    }
};

const toggleMenu = () => {
    showMenu.value = !showMenu.value;
};

const closeMenu = () => {
    showMenu.value = false;
};

onMounted(() => {
    bindOutsideClickListener();
});

onBeforeUnmount(() => {
    unbindOutsideClickListener();
});

const logoUrl = computed(() => {
    return `/layout/images/${layoutConfig.darkTheme.value ? 'logo-white' : 'logo-dark'}.svg`;
});

const onTopBarMenuButton = () => {
    topbarMenuActive.value = !topbarMenuActive.value;
};
const onSettingsClick = () => {
    topbarMenuActive.value = false;
    router.push('/documentation');
};
const topbarMenuClasses = computed(() => {
    return {
        'layout-topbar-menu-mobile-active': topbarMenuActive.value
    };
});

const bindOutsideClickListener = () => {
    if (!outsideClickListener.value) {
        outsideClickListener.value = (event) => {
            if (isOutsideClicked(event)) {
                topbarMenuActive.value = false;
            }
        };
        document.addEventListener('click', outsideClickListener.value);
    }
};
const unbindOutsideClickListener = () => {
    if (outsideClickListener.value) {
        document.removeEventListener('click', outsideClickListener);
        outsideClickListener.value = null;
    }
};
const isOutsideClicked = (event) => {
    if (!topbarMenuActive.value) return;

    const sidebarEl = document.querySelector('.layout-topbar-menu');
    const topbarEl = document.querySelector('.layout-topbar-menu-button');

    return !(sidebarEl.isSameNode(event.target) || sidebarEl.contains(event.target) || topbarEl.isSameNode(event.target) || topbarEl.contains(event.target));
};


</script>

<template>
    <div class="layout-topbar">
        <router-link to="/" class="layout-topbar-logo">
            <img :src="logoUrl" alt="logo" />
            <span>SULTAN HIGH TECH</span>
        </router-link>

        <button class="p-link layout-menu-button layout-topbar-button" @click="onMenuToggle()">
            <i class="pi pi-bars"></i>
        </button>

        <button class="p-link layout-topbar-menu-button layout-topbar-button" @click="onTopBarMenuButton()">
            <i class="pi pi-ellipsis-v"></i>
        </button>

        <div class="layout-topbar-menu" :class="topbarMenuClasses">
            <button @click="onTopBarMenuButton()" class="p-link layout-topbar-button">
                <i class="pi pi-calendar"></i>
                <span>Calendar</span>
            </button>
            <div class="profile-menu-wrapper">
                <button @click="toggleMenu" class="p-link layout-topbar-button">
                    <i class="pi pi-user"></i>
                    <span>Profile</span>
                </button>
                <div v-if="showMenu" class="profile-submenu">
                    <template v-if="!isLoggedIn">
                        <router-link to="/auth/login" @click="closeMenu">Login</router-link>
                        <router-link to="/auth/register" @click="closeMenu">Register</router-link>
                    </template>
                    <a v-else @click="logout">Logout</a>
                </div>
            </div>
            <button @click="onSettingsClick()" class="p-link layout-topbar-button">
                <i class="pi pi-cog"></i>
                <span>Settings</span>
            </button>
        </div>
    </div>
</template>

<style lang="scss" scoped>
.profile-menu-wrapper {
    position: relative;
}

.profile-submenu {
    position: absolute;
    top: 100%;
    right: 0;
    background-color: white;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 0.5rem 0;
    z-index: 1000;
}

.profile-submenu a {
    display: block;
    padding: 0.5rem 1rem;
    color: #333;
    text-decoration: none;
}

.profile-submenu a:hover {
    background-color: #f0f0f0;
}
</style>
