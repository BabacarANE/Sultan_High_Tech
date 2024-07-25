const state = () => ({
    token: null,
    user: null,
    client: null
});

const mutations = {
    SET_USER(state, user) {
        console.log('Setting user in state:', user);
        state.user = user;
    },
    SET_TOKEN(state, token) {
        console.log('Setting token in state:', token);
        state.token = token;
    },
    SET_CLIENT(state, client) {
        console.log('Setting client in state:', client);
        state.client = client;
    },
    LOGOUT(state) {
        state.user = null;
        state.token = null;
        state.client = null;
        console.log('User logged out, state cleared');
    }
};

const actions = {
    setUser({ commit }, user) {
        console.log('setUser action called with:', user);
        commit('SET_USER', user);
    },
    setToken({ commit }, token) {
        console.log('setToken action called with:', token);
        commit('SET_TOKEN', token);
    },
    setClient({ commit }, client) {
        console.log('setClient action called with:', client);
        commit('SET_CLIENT', client);
    },
    logout({ commit }) {
        console.log('logout action called');
        commit('LOGOUT');
    }
};

const getters = {
    getUser: state => state.user,
    getToken: state => state.token,
    getClient: state => state.client,
    isAdmin: state => state.user && state.user.role_id === 1,
    isUser: state => state.user && state.user.role_id === 2,
    isDeliveryService: state => state.user && state.user.role_id === 3,
    userRole: state => state.user ? state.user.role_id : null
};

export default {
    namespaced: true,
    state,
    mutations,
    actions,
    getters
};
