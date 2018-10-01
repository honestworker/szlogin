<template>
    <section id="wrapper">
        <div class="login-signup" style="background-image:url(/images/background/background.jpg);">
            <div class="login-box card">
            <div class="card-body">
                <form class="form-horizontal form-material" id="signform" @submit.prevent="submit">
                    <h3 class="box-title text-center m-b-20">Sign Up</h3>
                    <div class="form-group ">
                        <div class="col-xs-12">
                            <input type="text" name="group_name" class="form-control" placeholder="Group Name" v-model="signupForm.group_name">
                        </div>
                    </div>
                    <div class="form-group ">
                        <div class="col-xs-12">
                            <input type="text" name="org_num" class="form-control" placeholder="Organization Number" v-model="signupForm.org_num">
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-xs-12">
                            <input type="text" name="contact_name" class="form-control" placeholder="Contact Person" v-model="signupForm.contact_person">
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-xs-12">
                            <input type="text" name="phone_number" class="form-control" placeholder="Phone Number" v-model="signupForm.phone_number">
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-xs-12">
                            <input type="text" name="email" class="form-control" placeholder="Email" v-model="signupForm.email">
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-xs-12">
                            <input type="password" name="password" class="form-control" placeholder="Password" v-model="signupForm.password"> </div>
                    </div>
                    <div class="form-group">
                        <div class="col-xs-12">
                            <input type="password" name="password_confirmation" class="form-control" placeholder="Confirm Password" v-model="signupForm.password_confirmation"> </div>
                    </div>
                    <div class="form-group text-center m-t-20">
                        <div class="col-xs-12">
                            <button class="btn btn-info btn-lg btn-block text-uppercase waves-effect waves-light" type="submit">Sign Up</button>
                        </div>
                    </div>
                    <div class="form-group m-b-0">
                        <div class="col-sm-12 text-center">
                            <p>Already have an administrator account? <router-link to="/login" class="text-info m-l-5"><b>Sign In</b></router-link></p>
                        </div>
                    </div>
                    <div class="form-group m-b-0">
                        <div class="col-sm-12 text-center">
                            <p>Don't have an account? <router-link to="/register" class="text-info m-l-5"><b>Register</b></router-link></p>
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
                signupForm: {
                    group_name: '',
                    org_num: '',
                    contact_person: '',
                    phone_number: '',
                    email: '',
                    password: '',
                    password_confirmation: '',
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
                axios.post('/api/auth/signup', this.signupForm).then(response =>  {
                    toastr['success'](response.data.message);
                    this.$router.push('/success_signup');
                }).catch(error => {
                    toastr['error'](error.response.data.message);
                });
            }
        }
    }
</script>
