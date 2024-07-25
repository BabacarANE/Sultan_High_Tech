<script setup>
import { useLayout } from '@/layout/composables/layout';
import { ref, computed } from 'vue';
import AppConfig from '@/layout/AppConfig.vue';
import axios from 'axios';
import { useRouter } from 'vue-router';
import { useStore } from 'vuex';

const { layoutConfig } = useLayout();
const nom = ref('');
const prenom = ref('');
const email = ref('');
const numero_telephone = ref('');
const adresse = ref('');
const sexe = ref('');
const password = ref('');
const confirmPassword = ref('');
const errorMessage = ref('');

const logoUrl = computed(() => {
    return `/layout/images/${layoutConfig.darkTheme.value ? 'logo-white' : 'logo-dark'}.svg`;
});

const router = useRouter();
const store = useStore();

const handleRegister = async () => {
    if (password.value !== confirmPassword.value) {
        errorMessage.value = 'Passwords do not match.';
        return;
    }

    try {
        const response = await axios.post('http://127.0.0.1:8000/api/clients', {
            nom: nom.value,
            prenom: prenom.value,
            email: email.value,
            numero_telephone: numero_telephone.value,
            adresse: adresse.value,
            sexe: sexe.value,
            password: password.value
        });

        // Assuming the API returns a token and user data upon successful registration
        const { token, user, client } = response.data;

        // Store the token in localStorage
        localStorage.setItem('token', token);

        // Store user and client information in Vuex
        store.dispatch('user/setToken', token);
        store.dispatch('user/setUser', user);
        store.dispatch('user/setClient', client);

        // Redirect the user based on their role (if applicable)
        router.push({ path: '/' });

    } catch (error) {
        if (error.response && error.response.data && error.response.data.message) {
            errorMessage.value = error.response.data.message;
        } else {
            errorMessage.value = 'An error occurred during registration. Please try again later.';
        }
    }
};
</script>

<template>
    <div
        class="surface-ground flex align-items-center justify-content-center min-h-screen min-w-screen overflow-hidden">
        <div class="flex flex-column align-items-center justify-content-center">
            <div
                style="border-radius: 56px; padding: 0.3rem; background: linear-gradient(180deg, var(--primary-color) 10%, rgba(33, 150, 243, 0) 30%)">
                <div class="w-full surface-card py-8 px-5 sm:px-8" style="border-radius: 53px">
                    <div class="text-center mb-5">
                        <img :src="logoUrl" alt="Image" height="50" class="mb-3"/>
                        <div class="text-900 text-3xl font-medium mb-3">Create an Account</div>
                        <span class="text-600 font-medium">Sign up to get started</span>
                    </div>

                    <form @submit.prevent="handleRegister">
                        <div class="flex flex-column md:flex-row">
                            <div class="flex-1 mr-3">
                                <label for="nom" class="block text-900 text-xl font-medium mb-2">Last Name</label>
                                <InputText id="nom" type="text" placeholder="Last Name" class="w-full mb-3"
                                           style="padding: 1rem" v-model="nom"/>
                            </div>
                            <div class="flex-1">
                                <label for="prenom" class="block text-900 text-xl font-medium mb-2">First Name</label>
                                <InputText id="prenom" type="text" placeholder="First Name" class="w-full mb-3"
                                           style="padding: 1rem" v-model="prenom"/>
                            </div>
                        </div>

                        <label for="email" class="block text-900 text-xl font-medium mb-2">Email</label>
                        <InputText id="email" type="email" placeholder="Email address" class="w-full mb-3"
                                   style="padding: 1rem" v-model="email"/>

                        <label for="numero_telephone" class="block text-900 text-xl font-medium mb-2">Phone
                            Number</label>
                        <InputText id="numero_telephone" type="tel" placeholder="Phone Number" class="w-full mb-3"
                                   style="padding: 1rem" v-model="numero_telephone"/>

                        <label for="adresse" class="block text-900 text-xl font-medium mb-2">Address</label>
                        <InputText id="adresse" type="text" placeholder="Address" class="w-full mb-3"
                                   style="padding: 1rem" v-model="adresse"/>

                        <label for="sexe" class="block text-900 text-xl font-medium mb-2">Gender</label>
                        <Dropdown id="sexe" v-model="sexe" :options="['M', 'F']" placeholder="Select Gender"
                                  class="w-full mb-3"/>

                        <label for="password" class="block text-900 font-medium text-xl mb-2">Password</label>
                        <Password id="password" v-model="password" placeholder="Password" :toggleMask="true"
                                  class="w-full mb-3" inputClass="w-full" :inputStyle="{ padding: '1rem' }"></Password>

                        <label for="confirmPassword" class="block text-900 font-medium text-xl mb-2">Confirm
                            Password</label>
                        <Password id="confirmPassword" v-model="confirmPassword" placeholder="Confirm Password"
                                  :toggleMask="true" class="w-full mb-3" inputClass="w-full"
                                  :inputStyle="{ padding: '1rem' }"></Password>

                        <Button label="Register" class="w-full p-3 text-xl" type="submit"></Button>
                        <div v-if="errorMessage" class="mt-3 text-red-500">{{ errorMessage }}</div>
                    </form>
                    <div class="flex justify-content-between mt-5">
                        <Button label="Login" class="w-full p-3 text-xl mr-2" @click="$router.push('/auth/login')"></Button>
                        <Button label="Home" class="w-full p-3 text-xl ml-2" @click="$router.push('/')"></Button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <AppConfig simple/>
</template>

<style scoped>
.pi-eye {
    transform: scale(1.6);
    margin-right: 1rem;
}

.pi-eye-slash {
    transform: scale(1.6);
    margin-right: 1rem;
}
</style>
