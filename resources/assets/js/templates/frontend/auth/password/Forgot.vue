<template>
    <v-container
            id="full"
            fluid
            align-center
            justify-center
    >
        <v-card
                id="enter-card"
                width="300px"
        >
            <v-card
                    id="form-header"
                    color="primary"
            >
                <v-icon medium color="white">autorenew</v-icon>
                <h1 class="text-xs-center">{{ $t('content.frontend.auth.password.forgot.title') }}</h1>
            </v-card>

            <v-card-text>
                <p class="body-1 mb-0" v-html="$t('content.frontend.auth.password.forgot.description')"></p>
            </v-card-text>

            <v-form id="form">
                <v-text-field
                        v-model="email"
                        :label="$t('validation.attributes.email')"
                        required
                        prepend-icon="mail_outline"
                        @keyup.enter="perform"
                ></v-text-field>
                <vue-recaptcha
                        v-if="reCaptchaKey"
                        :sitekey="reCaptchaKey"
                        style="transform:scale(0.86);-webkit-transform:scale(0.86);transform-origin:0 0;
                            -webkit-transform-origin:0 0;"
                        @verify="setReCaptchaResponse"
                >
                </vue-recaptcha>
                <v-btn
                        @click="perform"
                        :loading="loadingBtn"
                        :disabled="disabledBtn"
                        block
                        color="primary"
                >
                    {{ $t('content.frontend.auth.password.forgot.continue') }}</v-btn>
            </v-form>

            <v-footer
                    height="auto"
                    id="form-footer"
            >
                <v-tooltip bottom>
                    <v-btn
                            large
                            outline
                            icon
                            color="green"
                            slot="activator"
                            :to="{name: 'frontend.auth.login'}"
                    >
                        <v-icon>vpn_key</v-icon>
                    </v-btn>
                    <span>{{ $t('content.frontend.auth.login.title') }}</span>
                </v-tooltip>

                <v-tooltip bottom v-if="!onlyForAdmins">
                    <v-btn
                            large
                            outline
                            icon
                            color="orange"
                            slot="activator"
                            :to="{name: 'frontend.auth.password.forgot'}"
                    >
                        <v-icon>shopping_cart</v-icon>
                    </v-btn>
                    <span>{{ $t('content.frontend.auth.login.purchase_without_auth') }}</span>
                </v-tooltip>
            </v-footer>
        </v-card>
    </v-container>
</template>

<script>
    import loader from './../../../../core/http/loader'
    import VueRecaptcha from 'vue-recaptcha';

    export default {
        data() {
            return {
                email: '',
                loadingBtn: false,

                onlyForAdmins: false,
                accessModeAny: false,
                accessModeAuth: false,
                reCaptchaKey: null,
                reCaptchaResponse: null
            }
        },
        beforeRouteEnter (to, from, next) {
            loader.beforeRouteEnter('/spa/password/forgot', to, from, next);
        },
        beforeRouteUpdate (to, from, next) {
            loader.beforeRouteUpdate('/spa/password/forgot', to, from, next, this);
        },
        computed: {
            disabledBtn() {
                return !this.check();
            }
        },
        methods: {
            setReCaptchaResponse(response) {
                this.reCaptchaResponse = response;
            },
            resetCaptcha() {
                if (this.reCaptchaKey) {
                    grecaptcha.reset();
                }
            },
            check() {
                return this.email !== '';
            },
            send() {
                this.loadingBtn = true;
                this.$axios.post('/spa/password/forgot', {
                    email: this.email,
                    _captcha: this.reCaptchaResponse
                })
                    .then(response => {
                        let data = response.data;
                        let status = data.status;
                        this.resetCaptcha();
                        if (status === 'success') {
                            this.$router.push({name: data.redirect});
                        }
                        this.loadingBtn = false;
                    })
                    .catch(() => {
                        this.resetCaptcha();
                        this.loadingBtn = false;
                    });
            },
            perform() {
                if (!this.check()) {
                    return;
                }
                this.send();
            },
            setData(response) {
                const data = response.data;

                this.onlyForAdmins = data.onlyForAdmins;
                this.accessModeAny = data.accessModeAny;
                this.accessModeAuth = data.accessModeAuth;
                this.reCaptchaKey = data.captchaKey;
            }
        },
        components: {
            'vue-recaptcha': VueRecaptcha
        }
    }
</script>
