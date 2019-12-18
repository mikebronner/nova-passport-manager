<script>
    import Multiselect from "vue-multiselect";

    export default {
        components: {
            "multiselect": Multiselect,
        },

        data: function () {
            return {
                accessToken: "",
                accessTokens: [],
                authorizedTokens: [],
                clientErrors: {},
                clientId: null,
                clientName: "",
                clients: [],
                redirectUrl: "",
                scopes: [],
                selectedScopes: [],
                showCreateClientButton: true,
                showUpdateClientButton: false,
                tokenErrors:{},
                tokenName: "",
            };
        },

        mounted() {
            this.getAuthorizedTokens();
            this.getClients();
            this.getAccessTokens();
            this.getScopes();
        },

        computed: {
        },

        methods: {
            clearAccessToken: function () {
                this.accessToken = "";
            },

            clientFieldHasError: function (field) {
                return (((this.clientErrors || {})[field] || []).join(" ").length > 0);
            },

            clientFieldError: function (field) {
                return ((this.clientErrors || {})[field] || []).join(" ");
            },

            tokenFieldHasError: function (field) {
                return (((this.clientErrors || {})[field] || []).join(" ").length > 0);
            },

            tokenFieldError: function (field) {
                return ((this.clientErrors || {})[field] || []).join(" ");
            },

            getAuthorizedTokens: function () {
                axios.get('/oauth/tokens')
                    .then(response => {
                        this.authorizedTokens = Object.assign([], response.data);
                    });
            },

            getAccessTokens: function () {
                axios.get('/oauth/personal-access-tokens')
                    .then(response => {
                        this.accessTokens = Object.assign([], response.data);
                    });
            },

            getScopes() {
                axios.get('/oauth/scopes')
                    .then(response => {
                        this.scopes = Object.assign([], response.data);
                    });
            },

            revokeAuthorizedToken: function (token) {
                axios.delete('/oauth/tokens/' + token.id)
                    .then(response => {
                        this.getAuthorizedTokens();
                    });
            },

            getClients: function () {
                axios.get('/oauth/clients')
                    .then(response => {
                        this.clients = Object.assign([], response.data);
                    });
            },

            storeClient: function () {
                this.persistClient(
                    'post',
                    '/oauth/clients'
                );
            },

            editClient: function (client) {
                this.clientId = client.id;
                this.clientName = client.name;
                this.redirectUrl = client.redirect;
                this.showUpdateClientButton = true;
                this.showCreateClientButton = false;
            },

            updateClient: function () {
                this.persistClient(
                    'put',
                    '/oauth/clients/' + this.clientId
                );

                this.showUpdateClientButton = false;
                this.showCreateClientButton = true;
            },

            persistClient(method, uri) {
                this.clientErrors = Object.assign({});

                axios[method](
                        uri,
                        {
                            name: this.clientName,
                            redirect: this.redirectUrl,
                        }
                    )
                    .then(response => {
                        this.getClients();
                        this.clientName = "";
                        this.redirectUrl = "";
                        this.clientErrors = Object.assign({});
                    })
                    .catch(error => {
                        if (typeof error.response.data === 'object') {
                            this.clientErrors = Object.assign({}, error.response.data.errors);
                        }
                    });
            },

            destroyClient(client) {
                axios.delete('/oauth/clients/' + client.id)
                    .then(response => {
                        this.getClients();
                    });
            },

            storeAccessToken: function () {
                this.accessToken = "";
                this.tokenErrors = Object.assign({});
                axios.post(
                        '/oauth/personal-access-tokens',
                        {
                            name: this.tokenName,
                            scopes: this.selectedScopes.map((scope) => {
                                return scope.id;
                            }),
                        }
                    )
                    .then(response => {
                        this.tokenName = '';
                        this.selectedScopes = Object.assign([]);
                        this.tokenErrors = Object.assign({});
                        this.accessToken = response.data.accessToken;
                        this.tokens.push(response.data.token);
                    })
                    .catch(error => {
                        this.tokenErrors = ['Something went wrong. Please try again.'];

                        if (typeof error.response.data === 'object') {
                            this.tokenErrors = Object.assign({}, error.response.data.errors);
                        }
                    });
            },

            revokeToken: function (token) {
                axios.delete('/oauth/personal-access-tokens/' + token.id)
                    .then(response => {
                        this.getAccessTokens();
                    });
            },
        },
    }
</script>

<template>
    <div>
        <h1>Paspport Manager</h1>
        <h2 class="mt-8 text-90 font-normal text-2xl">Authorized Applications</h2>
        <div
            class="card mt-3"
        >
            <p
                class="p-6 text-center text-gray-500"
                v-show="authorizedTokens.length === 0"
            >
                No OAuth Clients have connected yet.
            </p>
            <div
                v-show="authorizedTokens.length > 0"
                class="relative"
            >
                <div class="overflow-hidden overflow-x-auto relative rounded-lg">
                    <table cellpadding="0" cellspacing="0" data-testid="resource-table" class="table w-full">
                        <thead>
                            <tr>
                                <th class="w-16 text-center">ID</th>
                                <th class="text-center">Name</th>
                                <th class="text-center">Scopes</th>
                                <th>&nbsp;</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="token in authorizedTokens"
                                :key="token.id + '-' + token.client.name"
                            >
                                <td><span class="whitespace-no-wrap text-left" via-resource="" via-resource-id="">{{ token.id }}</span></td>
                                <td><span class="whitespace-no-wrap text-left" via-resource="" via-resource-id="">{{ token.client.name }}</span></td>
                                <td><span class="whitespace-no-wrap text-left" via-resource="" via-resource-id="">{{ token.scopes.join(', ') }}</span></td>
                                <td>
                                    <a
                                        class="action-link text-danger"
                                        @click="revoke(token)"
                                    >
                                        <svg
                                            class="fill-current h-3 w-3"
                                            aria-hidden="true"
                                            focusable="false"
                                            role="img"
                                            xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 448 512"
                                        >
                                            <path d="M268 416h24a12 12 0 0 0 12-12V188a12 12 0 0 0-12-12h-24a12 12 0 0 0-12 12v216a12 12 0 0 0 12 12zM432 80h-82.41l-34-56.7A48 48 0 0 0 274.41 0H173.59a48 48 0 0 0-41.16 23.3L98.41 80H16A16 16 0 0 0 0 96v16a16 16 0 0 0 16 16h16v336a48 48 0 0 0 48 48h288a48 48 0 0 0 48-48V128h16a16 16 0 0 0 16-16V96a16 16 0 0 0-16-16zM171.84 50.91A6 6 0 0 1 177 48h94a6 6 0 0 1 5.15 2.91L293.61 80H154.39zM368 464H80V128h288zm-212-48h24a12 12 0 0 0 12-12V188a12 12 0 0 0-12-12h-24a12 12 0 0 0-12 12v216a12 12 0 0 0 12 12z"></path>
                                        </svg>
                                    </a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>


        <h2 class="mt-8 text-90 font-normal text-2xl">OAuth Clients</h2>
        <div class="card mt-3">
            <div class="flex border-b border-40">
                <div class="w-1/5 py-6 px-8">
                    <label for="create-client-name" class="inline-block text-80 pt-2 leading-tight">Name</label>
                </div>
                <div class="py-6 px-8 w-1/2">
                    <input type="hidden" v-model="clientId">
                    <input
                        v-model="clientName"
                        type="text"
                        placeholder="Name"
                        class="w-full form-control form-input form-input-bordered"
                        :class="{'border-danger': clientFieldHasError('name')}"
                    >
                    <div
                        v-show="clientFieldHasError('name')"
                        class="help-text error-text mt-2 text-danger"
                        v-text="clientFieldError('name')"
                    ></div>
                    <div class="help-text help-text mt-2">
                        Something your users will recognize and trust.
                    </div>
                </div>
            </div>
            <div class="flex border-b border-40">
                <div class="w-1/5 py-6 px-8">
                    <label for="create-redirect-url" class="inline-block text-80 pt-2 leading-tight">Redirect URL</label>
                </div>
                <div class="py-6 px-8 w-1/2">
                    <input
                        v-model="redirectUrl"
                        type="text"
                        placeholder="Redirect URL"
                        class="w-full form-control form-input form-input-bordered"
                        :class="{'border-danger': clientFieldHasError('redirect')}"
                    >
                    <div
                        v-show="clientFieldHasError('redirect')"
                        class="help-text error-text mt-2 text-danger"
                        v-text="clientFieldError('redirect')"
                    ></div>
                    <div class="help-text help-text mt-2">
                        Your application's authorization callback URL.
                    </div>
                </div>
            </div>
            <div class="flex items-center">
                <div class="w-1/5 py-3 px-8"></div>
                <div class="pt-6 pb-6 px-8 w-1/2">
                    <button
                        v-show="showUpdateClientButton"
                        type="submit"
                        class="btn btn-default btn-primary inline-flex items-center relative"
                        @click="updateClient"
                    >
                        Update Client
                    </button>
                    <button
                        v-show="showCreateClientButton"
                        type="submit"
                        class="btn btn-default btn-primary inline-flex items-center relative"
                        @click="storeClient"
                    >
                        Create OAuth Client
                    </button>
                </div>
            </div>
        </div>

        <div class="card mt-3 overflow-hidden">
            <p
                class="p-6 text-center text-gray-500"
                v-show="clients.length === 0"
            >
                You have not yet created any OAuth clients.
            </p>

            <table
                class="table w-full rounded"
                v-show="clients.length > 0"
            >
                <thead>
                    <tr>
                        <th class="w-16">ID</th>
                        <th>Name</th>
                        <th>Secret</th>
                        <th class="w-24"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="client in clients">
                        <td class="text-center">
                            {{ client.id }}
                        </td>
                        <td>
                            {{ client.name }}
                        </td>
                        <td>
                            <code>{{ client.secret }}</code>
                        </td>
                        <td class="text-right">
                            <a
                                class="cursor-pointer inline"
                                tabindex="-1"
                                @click="editClient(client)"
                            >
                                <svg
                                    class="fill-current ml-3"
                                    aria-hidden="false"
                                    focusable="true"
                                    role="img"
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 576 512"
                                    width="20"
                                    height="20"
                                >
                                    <path d="M402.3 344.9l32-32c5-5 13.7-1.5 13.7 5.7V464c0 26.5-21.5 48-48 48H48c-26.5 0-48-21.5-48-48V112c0-26.5 21.5-48 48-48h273.5c7.1 0 10.7 8.6 5.7 13.7l-32 32c-1.5 1.5-3.5 2.3-5.7 2.3H48v352h352V350.5c0-2.1.8-4.1 2.3-5.6zm156.6-201.8L296.3 405.7l-90.4 10c-26.2 2.9-48.5-19.2-45.6-45.6l10-90.4L432.9 17.1c22.9-22.9 59.9-22.9 82.7 0l43.2 43.2c22.9 22.9 22.9 60 .1 82.8zM460.1 174L402 115.9 216.2 301.8l-7.3 65.3 65.3-7.3L460.1 174zm64.8-79.7l-43.2-43.2c-4.1-4.1-10.8-4.1-14.8 0L436 82l58.1 58.1 30.9-30.9c4-4.2 4-10.8-.1-14.9z"></path>
                                </svg>
                            </a>
                            <a
                                class="inline cursor-pointer text-danger"
                                @click="destroyClient(client)"
                            >
                                <svg
                                    class="fill-current ml-3"
                                    aria-hidden="true"
                                    focusable="false"
                                    role="img"
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 448 512"
                                    width="20"
                                    height="20"
                                >
                                    <path d="M268 416h24a12 12 0 0 0 12-12V188a12 12 0 0 0-12-12h-24a12 12 0 0 0-12 12v216a12 12 0 0 0 12 12zM432 80h-82.41l-34-56.7A48 48 0 0 0 274.41 0H173.59a48 48 0 0 0-41.16 23.3L98.41 80H16A16 16 0 0 0 0 96v16a16 16 0 0 0 16 16h16v336a48 48 0 0 0 48 48h288a48 48 0 0 0 48-48V128h16a16 16 0 0 0 16-16V96a16 16 0 0 0-16-16zM171.84 50.91A6 6 0 0 1 177 48h94a6 6 0 0 1 5.15 2.91L293.61 80H154.39zM368 464H80V128h288zm-212-48h24a12 12 0 0 0 12-12V188a12 12 0 0 0-12-12h-24a12 12 0 0 0-12 12v216a12 12 0 0 0 12 12z"></path>
                                </svg>
                            </a>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <h2 class="mt-8 text-90 font-normal text-2xl">Access Tokens</h2>
        <div
            v-show="accessToken.length == 0"
            class="card mt-3"
        >
            <div class="flex border-b border-40">
                <div class="w-1/5 py-6 px-8">
                    <label for="access-token-name" class="inline-block text-80 pt-2 leading-tight">Name</label>
                </div>
                <div class="py-6 px-8 w-1/2">
                    <input
                        v-model="tokenName"
                        id="access-token-name"
                        type="text"
                        placeholder="Name"
                        class="w-full form-control form-input form-input-bordered"
                        :class="{'border-danger': tokenFieldHasError('name')}"
                    >
                    <div
                        v-show="tokenFieldHasError('name')"
                        class="help-text error-text mt-2 text-danger"
                        v-text="tokenFieldError('name')"
                    ></div>
                </div>
            </div>
            <div
                class="flex border-b border-40"
            >
                <div class="w-1/5 py-6 px-8">
                    <label for="token-scopes" class="inline-block text-80 pt-2 leading-tight">Scopes</label>
                </div>
                <div class="py-6 px-8 w-1/2">
                    <multiselect
                        id="token-scopes"
                        v-model="selectedScopes"
                        :options="scopes"
                        :multiple="true"
                        :hide-selected="true"
                        :close-on-select="false"
                        :clear-on-select="false"
                        :preserve-search="false"
                        :internal-search="false"
                        placeholder="Select scopes..."
                        label="description"
                        track-by="id"
                        :preselect-first="false"
                        :class="{'border-danger': tokenFieldHasError('scopes')}"
                    ></multiselect>
                    <div
                        v-show="tokenFieldHasError('scopes')"
                        class="help-text error-text mt-2 text-danger"
                        v-text="tokenFieldError('scopes')"
                    ></div>
                </div>
            </div>
            <div class="flex items-center">
                <div class="w-1/5 py-3 px-8"></div>
                <div class="pt-6 pb-6 px-8 w-1/2">
                    <button
                        type="submit"
                        class="btn btn-default btn-primary inline-flex items-center relative"
                        @click="storeAccessToken"
                    >
                        Create Access Token
                    </button>
                </div>
            </div>
        </div>
        <div
            v-show="accessToken.length > 0"
            class="card mt-3"
        >
            <div class="flex border-b border-40 remove-bottom-border">
                <div class="w-1/5 py-6 px-8">
                    <label for="accessToken" class="inline-block text-80 pt-2 leading-tight">
                        New Access Token
                    </label>
                </div>
                <div class="py-6 px-8 w-4/5">
                    <textarea
                        id="accessToken"
                        rows="5"
                        class="w-full form-control form-input form-input-bordered py-3 h-auto"
                        v-text="accessToken"
                    ></textarea>
                    <div class="help-text help-text mt-2">
                        Here is your new access token. <strong>This is the only time it will be shown so don't lose it!</strong> You may now use this token to make API requests.
                    </div>
                </div>
            </div>
            <div class="flex items-center">
                <div class="w-1/5 pt-0 pb-6 px-8"></div>
                <div class="pt-0 pb-6 px-8 w-1/2">
                    <button
                        type="submit"
                        class="btn btn-default btn-danger inline-flex items-center relative"
                        @click="clearAccessToken"
                    >
                        Close
                    </button>
                </div>
            </div>
        </div>

        <div
            class="card mt-3"
        >
            <p
                class="p-6 text-center text-gray-500"
                v-show="accessTokens.length === 0"
            >
                You have not yet created any Access Tokens.
            </p>
            <div
                v-show="accessTokens.length > 0"
                class="relative"
            >
                <div class="overflow-hidden overflow-x-auto relative rounded-lg">
                    <table cellpadding="0" cellspacing="0" data-testid="resource-table" class="table w-full">
                        <thead>
                            <tr>
                                <th class="text-center">Name</th>
                                <th class="text-center">Expires At</th>
                                <th class="text-center">Scopes</th>
                                <th class="w-16">&nbsp;</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="token in accessTokens"
                                :key="token.id"
                            >
                                <td><span class="whitespace-no-wrap text-left" via-resource="" via-resource-id="">{{ token.name }}</span></td>
                                <td><span class="whitespace-no-wrap text-right" via-resource="" via-resource-id="">{{ token.expires_at }}</span></td>
                                <td><span class="whitespace-no-wrap text-right" via-resource="" via-resource-id="">{{ token.scopes.join(", ") }}</span></td>
                                <td class="whitespace-no-wrap text-right">
                                    <a
                                        class="cursor-pointer text-danger"
                                        @click="revokeToken(token)"
                                    >
                                        <svg
                                            class="fill-current"
                                            aria-hidden="true"
                                            focusable="false"
                                            role="img"
                                            xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 448 512"
                                            width="20"
                                            height="20"
                                        >
                                            <path d="M268 416h24a12 12 0 0 0 12-12V188a12 12 0 0 0-12-12h-24a12 12 0 0 0-12 12v216a12 12 0 0 0 12 12zM432 80h-82.41l-34-56.7A48 48 0 0 0 274.41 0H173.59a48 48 0 0 0-41.16 23.3L98.41 80H16A16 16 0 0 0 0 96v16a16 16 0 0 0 16 16h16v336a48 48 0 0 0 48 48h288a48 48 0 0 0 48-48V128h16a16 16 0 0 0 16-16V96a16 16 0 0 0-16-16zM171.84 50.91A6 6 0 0 1 177 48h94a6 6 0 0 1 5.15 2.91L293.61 80H154.39zM368 464H80V128h288zm-212-48h24a12 12 0 0 0 12-12V188a12 12 0 0 0-12-12h-24a12 12 0 0 0-12 12v216a12 12 0 0 0 12 12z"></path>
                                        </svg>
                                    </a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>
