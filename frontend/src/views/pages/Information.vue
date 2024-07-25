<template>
    <div class="user-info">
        <h2>Informations de l'utilisateur</h2>
        <div v-if="user">
            <p><strong>Nom :</strong> {{ user.nom }}</p>
            <p><strong>Prenom :</strong> {{ user.prenom }}</p>
            <p><strong>Email :</strong> {{ user.email }}</p>
            <!-- Ajoutez d'autres champs selon les informations disponibles dans votre store -->
        </div>
        <div v-else>
            <p>Aucun utilisateur connecté</p>
        </div>
    </div>
</template>

<script>
import { useStore } from 'vuex'
import { computed } from 'vue'

export default {
    name: 'UserInfo',
    setup() {
        const store = useStore()

        const user = computed(() => store.getters['user/getUser'])

        return {
            user
        }
    }
}
</script>

<style scoped>
.user-info {
    padding: 20px;
    border: 1px solid #ccc;
    border-radius: 5px;
}
</style>
