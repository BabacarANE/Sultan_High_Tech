const state = () => ({
    token: null,
    user: null,
    client: null
});

const mutations = {
    SET_USER(state, user) {
        state.user = user;
    },
    SET_TOKEN(state, token) {
        state.token = token;
    },
    SET_CLIENT(state, client) {
        state.client = client;
    },
    LOGOUT(state) {
        state.user = null;
        state.token = null;
        state.client = null;
    }
};

const actions = {
    setUser({ commit }, user) {
        commit('SET_USER', user);
    },
    setToken({ commit }, token) {
        commit('SET_TOKEN', token);
    },
    setClient({ commit }, client) {
        commit('SET_CLIENT', client);
    },
    logout({ commit }) {
        commit('LOGOUT');
    }
};

const getters = {
    getUser(state) {
        return state.user;
    },
    getToken(state) {
        return state.token;
    },
    getClient(state) {
        return state.client;
    }
};

export default {
    namespaced: true,
    state,
    mutations,
    actions,
    getters
};
