<template>
    <section id="wrapper">
        <div class="login-register" style="background-image:url(/images/background/background.jpg);">
            <div class="login-box card">
            <div class="card-body">
                <form class="form-horizontal form-material" id="passwordform" @submit.prevent="submit">
                    <h3 class="box-title m-b-20">Reset Password</h3>
                    <div class="form-group ">
                        <div class="col-xs-12">
                            <input type="text" name="email" class="form-control" placeholder="Email" v-model="passwordForm.email"> </div>
                    </div>
                    <div class="form-group text-center m-t-20">
                        <div class="col-xs-12">
                            <button class="btn btn-info btn-lg btn-block text-uppercase waves-effect waves-light" type="submit">Reset Password</button>
                        </div>
                    </div>
                    <div class="form-group m-b-0">
                        <div class="col-sm-12 text-center">
                            <p>Back to Login? <router-link to="/login" class="text-info m-l-5"><b>Sign In</b></router-link></p>
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
                passwordForm: {
                    email: ''
                }
            }
        },
        components: {
            GuestFooter
        },
        mounted(){
        },
        methods: {
            submit(e){
                axios.post('/auth/forgot-password', this.passwordForm).then(response =>  {
                    toastr['success'](response.data.message);
                    this.$router.push('/login');
                }).catch(error => {
                    if (error.message) {
                        toastr['error']('An unexpected error occurred!');
                        console.log(error.message);
                    }
                });
            }
        }
    }
</script>
