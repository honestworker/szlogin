<template>
    <form @submit.prevent="proceed">
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label for="">Name</label>
                    <input class="form-control" type="text" value="" v-model="groupName">
                </div>
                <div class="form-group">
                    <label for="">Description</label>
                    <textarea class="form-control" type="text" value="" v-model="groupDescription" rows="10"></textarea>
                </div>
            </div>
        </div>
        <button class="btn btn-info waves-effect waves-light m-t-10">
            <span v-if="groupId" @click="updateGroup">Update</span>
            <span v-else @click="storeGroup">Create</span>
        </button>
        <button class="btn btn-info waves-effect waves-light m-t-10" v-show="groupId" @click="cancelGroup">Cancel</button>
    </form>
</template>

<script>
    import helper from '../../services/helper'

    export default {
        data() {
            return {
                groupForm: new Form({
                    'name' : '',
                    'description' : '',
                }),
                myId: 0
            };
        },
        props: ['groupId', 'groupName', 'groupDescription'],
        mounted() {
            if(this.groupId != 0) {
                this.getGroups();
            }
        },
        methods: {
            proceed(){
            },
            cancelGroup(){
                if(this.groupId) {
                    this.$emit('interface');
                }
            },
            storeGroup(){
                this.groupForm.post('/api/group')
                .then(response => {
                    toastr['success'](response.message);
                    this.$emit('completed',response.group)
                })
                .catch(response => {
                    toastr['error'](response.message);
                });
            },
            updateGroup(){
                this.groupForm.patch('/api/group/'+this.groupId)
                .then(response => {
                    if(response.type == 'error')
                        toastr['error'](response.message);
                    else {
                        this.$router.push('/group');
                    }
                })
                .catch(response => {
                    toastr['error'](response.message);
                });
            }
        }
    }
</script>
<style>
    .slider{
        width: 100%;
    }
</style>
