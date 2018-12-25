<template>
    <section id="wrapper">
        <div class="register-sucess" style="background-image:url(/images/background/background.jpg);">
            <div class="login-box card">
            <div class="card-body">
                <form class="form-horizontal form-material" id="registerform" @submit.prevent="submit">
                    <h3 class="box-title text-center m-b-20">Congratulations</h3>
                    <div class="form-group m-b-0">
                        <div class="col-sm-12 text-center">
                            <p>You have registered successfully.</p>
                        </div>
                    </div>
                    <div class="form-group m-b-0">
                        <div class="col-sm-12 text-center">
                            <p>Do have an aministrator account?</p>
                            <p><router-link to="/login" class="text-info m-l-5"><b>Sign In</b></router-link></p>
                        </div>
                    </div>
                    <div class="form-group m-b-0">
                        <div class="col-sm-12 text-center">
                            <p>Do you want to register another account?</p>
                            <p><router-link to="/register" class="text-info m-l-5"><b>Register</b></router-link></p>
                        </div>
                    </div>
                </form>
            </div>
            <guest-footer></guest-footer>
          </div>
        </div>
    </section>
</template>

<script>
    import GuestFooter from '../../layouts/guest-footer.vue'

    export default {
        data() {
            return {
            }
        },
        components: {
            GuestFooter
        },
        mounted(){
        },
        methods: {
        }
    }
</script>