<template>
    <section id="wrapper">
        <div class="login-register" style="background-image:url(/images/background/background.jpg);">
            <div class="login-box card">
            <div class="card-body">
                <form class="form-horizontal form-material" id="registerform" @submit.prevent="submit">
                    <h3 class="box-title text-center m-b-20">Register</h3>
                    <div class="form-group ">
                        <div class="col-xs-12">
                            <input type="text" name="group_name" class="form-control" placeholder="Group Name" v-model="registerForm.group_name">
                        </div>
                    </div>
                    <div class="form-group ">
                        <div class="col-xs-12">
                            <input type="text" name="org_num" class="form-control" placeholder="Organization Number" v-model="registerForm.org_num">
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-xs-12">
                            <input type="text" name="contact_name" class="form-control" placeholder="Contact Person" v-model="registerForm.contact_person">
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-xs-12">
                            <input type="text" name="phone_number" class="form-control" placeholder="Phone Number" v-model="registerForm.phone_number">
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-xs-12">
                            <input type="text" name="email" class="form-control" placeholder="Email" v-model="registerForm.email">
                        </div>
                    </div>
                    <div class="form-group text-center m-t-20">
                        <div class="col-xs-12">
                            <button class="btn btn-info btn-lg btn-block text-uppercase waves-effect waves-light" type="submit">Register</button>
                        </div>
                    </div>
                    <div class="form-group m-b-0">
                        <div class="col-sm-12 text-center">
                            <p>Already have an account?</p>
                            <p><router-link to="/login" class="text-info m-l-5"><b>Log In</b></router-link></p>
                        </div>
                    </div>
                    <div class="form-group m-b-0">
                        <div class="col-sm-12 text-center">
                            <p>Don't have an account?</p>
                            <p><router-link to="/signup" class="text-info m-l-5"><b>Sign Up</b></router-link></p>
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
                registerForm: {
                    group_name: '',
                    org_num: '',
                    contact_person: '',
                    phone_number: '',
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
                axios.post('/api/auth/register', this.registerForm).then(response =>  {
                    toastr['success'](response.data.message);
                    this.$router.push('/success_register');
                }).catch(error => {
                    toastr['error'](error.response.data.message);
                });
            }
        }
    }
</script>
