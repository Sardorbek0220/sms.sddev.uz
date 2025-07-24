<!DOCTYPE html>
<html lang="en">
<head>
	<meta name="robots" content="noindex">
	<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
	<script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
	<link href="https://fonts.googleapis.com/css?family=Roboto:100,300,400,500,700,900" rel="stylesheet">
  	<link href="https://cdn.jsdelivr.net/npm/@mdi/font@6.x/css/materialdesignicons.min.css" rel="stylesheet">
  	<link href="/assets/other/bootstrap.min.css" rel="stylesheet">
  	<link href="/assets/other/vuetify.min.css" rel="stylesheet">
	<meta charset="UTF-8">
	<link rel="icon" href="../assets/logo.png">
	<title>Live | Sales Doctor</title>
</head>
<body>
	
<div id="app" class="content-wrapper">
    <v-container fluid class="lighten-5">
        <v-row>
            <v-col md="4" style="margin-left: 10%;">
                <v-simple-table>
                    <template v-slot:default>
                        <thead style="border: solid 1px grey;">
                            <tr>
                                <th class="text-center" width="250px" style="font-size: 20px">Ibox</th>
                            </tr>
                        </thead>
                        <tbody style="border: solid 1px grey;">
                            <tr v-for="user in users.filter((u) => availableOperators.includes(u.num) && u.num != '')">
                                <td style="background: firebrick; color: white" :id="'num_' + user?.num">({{ user?.num }}) {{ user?.name }}</td>
                            </tr>
                        </tbody>    
                    </template>
                </v-simple-table>
            </v-col>
            <v-col md="4" style="margin-left: 12%;">
                <v-simple-table>
                    <template v-slot:default>
                        <thead style="border: solid 1px grey;">
                            <tr>
                                <th class="text-center" width="250px" style="font-size: 20px">Sales Doctor</th>
                            </tr>
                        </thead>
                        <tbody style="border: solid 1px grey;">
                            <tr v-for="user in users.filter((u) => availableOperatorsSD.includes(u.num) && u.num != '')">
                                <td style="background: firebrick; color: white" :id="'num_' + user?.num">({{ user?.num }}) {{ user?.name }}</td>
                            </tr>
                        </tbody>    
                    </template>
                </v-simple-table>
            </v-col>
        </v-row>
    </v-container>
</div>

<script src="/assets/other/axios.min.js"></script>
<script src="/assets/other/vue.js"></script>
<script src="/assets/other/vuetify.js"></script>

<script>

	const statusColors = {
		registered: 'forestgreen',
		unregistered: 'firebrick',
		ringing: 'forestgreen',
		hangup: 'forestgreen',
		answered: 'forestgreen',
		register: 'forestgreen',
		unregister: 'firebrick',
		pre_register: 'forestgreen',
		register_attempt: 'forestgreen',
	}
	
	const exampleSocket = new WebSocket("wss://pbx12127.onpbx.ru:3342/?key=<?= $auth_key ?>");

	exampleSocket.onopen = function (e) {
		let mes = {
			"command": "subscribe",
			"reqId": "123123",
			"data": {
				"eventGroups": [
					"user_blf",
					"user_registration"
				]
			}
		}
		exampleSocket.send(JSON.stringify(mes))
	}

	exampleSocket.onmessage = function (e) {
		var data = JSON.parse(e.data)
		var span = document.getElementById("num_"+data.data.uid);
		if (data.event == 'user_blf' && span != null) {
			span.style.background=statusColors[data.data.status];
		}
		if (data.event == 'user_registration' && span != null) {
			span.style.background=statusColors[data.data.state];
		}
	}

	const app = new Vue({
	  	el: '#app',
	  	vuetify: new Vuetify(),
	  	data: {
            availableOperatorsSD: [],
            availableOperators: [],
            users: []
        },
        async mounted (){
            await this.getFifo();
            await this.getUsers();
            await this.getOperatorCondition();
        },
	  	methods: {
			async getOperatorCondition(){
				await axios.get('/admin/monitoring/operatorCondition', {params: {date: (new Date()).toISOString().split('T')[0]}}).then(response => {
					if (response.status == 200) {		
						try {							
							for (const id in response.data.calls) {
								const data = response.data.calls[id]
								const span = document.getElementById("num_"+data.uid);								
								if (span != null) {
									span.style.background=statusColors['register']
								}
							}
						} catch (error) {
							console.log(error);
						}
					}
				});	
			},
            async getUsers(){
				await axios.get('/admin/monitoring/users').then(response => {
					if (response.status == 200) {
						this.users = response.data
                        this.users.sort((a, b) => a.name.localeCompare(b.name))                     
					}
				});
		  	},
		  	async getFifo(){
		  	    let response = await axios({
				    method: 'post',
				    url: "https://api2.onlinepbx.ru/pbx12127.onpbx.ru/fifo/get.json",
				    data: {
				    	asd: 'asdad'
				    },
				    headers: {
				        "x-pbx-authentication": "<?= $key_and_id ?>"
				    }
				});

                for (const datum of response.data.data) {
                    if (datum.num == '5201') {
						this.availableOperatorsSD = datum.users.split(":1;");
					}
                    if (datum.num == '5202') {
						this.availableOperators = datum.users.split(":1;");
					}
                }
		  	},
	  	},
	})
</script>	
</body>
</html>