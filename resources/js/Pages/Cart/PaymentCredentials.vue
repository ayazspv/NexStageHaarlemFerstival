<template>
    <div id="app" class="container">
        <h2 class="my-4">User Information</h2>
        <form @submit.prevent="generateJson" class="bg-light p-4 rounded shadow-sm">
            <div class="form-group">
                <label for="name">Name:</label>
                <input type="text" v-model="name" id="name" class="form-control" required />
            </div>

            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" v-model="email" id="email" class="form-control" required />
            </div>

            <button type="submit" class="btn btn-primary btn-block">
                Submit
            </button>
        </form>

        <div v-if="jsonOutput" class="mt-4">
            <h4>Generated JSON:</h4>
            <pre class="bg-light p-3 rounded">{{ jsonOutput }}</pre>
            <button class="btn btn-success" @click="downloadJson">
                Download JSON
            </button>
        </div>
    </div>
</template>

<script>


export default {
    data() {
        return {
            name: '',
            email: '',
            jsonOutput: null
        };
    },
    methods: {
        generateJson() {
            // Generate a random cartId (you can replace this with any ID generation method you prefer)
            const cartId = this.generateRandomId();


            // Structure the JSON
            const jsonData = {
                cartId,
                user: {
                    name: this.name,
                    email: this.email
                },
                success_url: '/success',
                fail_url: '/failed'
            };

            // Update the jsonOutput data to show the generated JSON
            this.jsonOutput = JSON.stringify(jsonData, null, 2);
        },
        generateRandomId() {
            // Generate a random ID (you can customize the format of the ID)
            return 'cart_' + Math.random().toString(36).substr(2, 9);
        },
        downloadJson() {
            // Create a Blob object with JSON data
            const blob = new Blob([JSON.stringify(this.jsonOutput)], { type: 'application/json' });

            // Create a link element for downloading the file
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = 'cart_data.json';
            link.click();
        }
    }
};


</script>

<style scoped>
/* Add custom styles here if needed */
</style>