<template>
    <div class="container">
        <div class="row mt-5">
          <div class="col-md-12">
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">Users</h3>

                <div class="card-tools">
                    <button class="btn btn-success" data-toggle="modal" data-target="#addNewUser">
                        Add New
                        <i class="fas fa-user-plus fa-fw"></i>
                    </button>
                </div>
              </div>
              <!-- /.card-header -->
              <div class="card-body table-responsive p-0">
                <table class="table table-hover text-nowrap">
                  <thead>
                    <tr>
                      <th>ID</th>
                      <th>Name</th>
                      <th>Email</th>
                      <th>Type</th>
                      <th>Registered At</th>
                      <th>Modify</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="user in users">
                      <td>{{user.id}}</td>
                      <td>{{user.name | capitalaizeFirstText}}</td>
                      <td>{{user.email}}</td>
                      <td><span class="tag tag-success">{{user.type |capitalaizeFirstText}}</span></td>
                      <td>{{user.created_at }}</td>
                      <td>
                          <a href="#" @click="deleteUser(user.id)">
                              <i class="fas fa-edit text-orange"></i>
                          </a>
                          <a href="#">
                              <i class="fa fa-trash text-red"></i>
                          </a>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <!-- /.card-body -->
            </div>
            <!-- /.card -->
          </div>
        </div>
        <!-- /.row -->
        <div class="modal" id="addNewUser" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add New User</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form @submit.prevent="createUser" @keydown="form.onKeydown($event)">
                    <div class="modal-body">
                        <!-- name input -->
                        <div class="form-group">
                            <input v-model="form.name" type="text" name="name" class="form-control" :class="{ 'is-invalid': form.errors.has('name') }" placeholder="Name">
                            <has-error :form="form" field="name"></has-error>
                        </div>
                        <!-- email input -->
                        <div class="form-group">
                            <input v-model="form.email" type="email" name="email" class="form-control" :class="{ 'is-invalid': form.errors.has('email') }" placeholder="Email">
                            <has-error :form="form" field="email"></has-error>
                        </div>
                        <!-- password input -->
                        <div class="form-group">
                            <input v-model="form.password" type="password" name="password"
                                class="form-control" :class="{ 'is-invalid': form.errors.has('password') }" placeholder="Password">
                            <has-error :form="form" field="password"></has-error>
                        </div>
                        <!-- type input -->
                        <div class="form-group">
                            <select v-model="form.type" type="text" name="type" id="type" class="form-control" :class="{ 'is-invalid': form.errors.has('type') }" placeholder="Type">
                                <option value="">Select User Role</option>
                                <option value="admin">Admin</option>
                                <option value="user">Standard User</option>
                                <option value="author">Author</option>
                            </select>                                    
                            <has-error :form="form" field="type"></has-error>
                        </div>
                        <!-- bio input -->
                        <div class="form-group">
                            <textarea v-model="form.bio" name="bio" id="bio" class="form-control" :class="{ 'is-invalid': form.errors.has('bio') }" placeholder="Short Bio (Optional)"></textarea>
                            <has-error :form="form" field="bio"></has-error>
                        </div>
                        <!-- photo input -->
                        <div class="form-group">
                            <input v-model="form.photo" type="text" name="photo" class="form-control" :class="{ 'is-invalid': form.errors.has('photo') }" placeholder="Photo">
                            <has-error :form="form" field="photo"></has-error>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button :disabled="form.busy" type="submit" class="btn btn-primary">Create</button>
                        <button type="button" v-on:click="resetNewUserForm" class="btn btn-warning">Reset</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        data(){
            return{
                users: {}, 
                form: new Form({
                    name: '',
                    email: '',
                    password: '', 
                    type: '',
                    bio: '',
                    photo: '',           
                })
            }
        },
        mounted() {
            console.log('Component mounted.')
        },
        methods: {
            createUser(){  
                this.$Progress.start();
                 // Submit the form via a POST request
                this.form.post('api/users')
                    .then(({ data }) => { 
                        if(this.form.successful){
                            $('#addNewUser').modal('hide');
                            Toast.fire({
                                icon: 'success',
                                title: 'User created successfully'
                            })
                            fireEvent.$emit('newUserCreated');
                            // this.loadUsers();
                            this.$Progress.finish();
                        }
                    });
            },
            loadUsers(){
                this.$Progress.start();
                axios.get('api/users').then(({ data }) => {
                    this.users = data.data;
                    console.log(data);
                    this.$Progress.finish();
                });
            },
            resetNewUserForm(){
                this.form.reset();
            },
            deleteUser(){
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.value) {
                        this.$Progress.start();
                        axios.delete('api/users').then(({ data }) => {
                            this.users = data;
                            console.log(data);
                            this.$Progress.finish();
                        });
                        Swal.fire(
                            'Deleted!',
                            'Your file has been deleted.',
                            'success'
                            )
                    }
                })

            }
        },
        created(){
            this.loadUsers();
            // setInterval(()=>this.loadUsers(),3000);
            fireEvent.$on('newUserCreated', ()=>{
                this.loadUsers();
            });
        }
    }
</script>
