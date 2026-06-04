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
	<style>
		body {
			margin: 0;
			background:
				radial-gradient(circle at top left, rgba(37, 99, 235, 0.12), transparent 28%),
				linear-gradient(180deg, #eef4ff 0%, #f8fafc 100%);
			color: #0f172a;
		}
		.live-shell {
			min-height: 100vh;
			padding: 24px 18px 30px !important;
		}
		.live-toolbar {
			display: flex;
			flex-wrap: wrap;
			align-items: center;
			justify-content: space-between;
			gap: 14px;
			padding: 18px 20px;
			border-radius: 22px;
			background: rgba(255, 255, 255, 0.96);
			border: 1px solid rgba(148, 163, 184, 0.20);
			box-shadow: 0 18px 38px rgba(15, 23, 42, 0.08);
		}
		.live-title {
			font-size: 28px;
			font-weight: 800;
			line-height: 1.1;
		}
		.live-subtitle {
			margin-top: 6px;
			font-size: 13px;
			color: #64748b;
			font-weight: 500;
		}
		.live-toolbar-meta {
			display: inline-flex;
			flex-wrap: wrap;
			gap: 10px;
		}
		.live-badge {
			display: inline-flex;
			align-items: center;
			padding: 8px 12px;
			border-radius: 999px;
			background: #e2e8f0;
			color: #334155;
			font-size: 12px;
			font-weight: 700;
		}
		.live-badge--ok {
			background: #dcfce7;
			color: #166534;
		}
		.live-badge--warn {
			background: #fef3c7;
			color: #92400e;
		}
		.live-alert {
			margin-top: 12px;
			padding: 12px 14px;
			border-radius: 14px;
			font-size: 13px;
			font-weight: 700;
		}
		.live-alert--warning {
			background: #fff7ed;
			color: #9a3412;
			border: 1px solid #fdba74;
		}
		.live-alert--error {
			background: #fef2f2;
			color: #b91c1c;
			border: 1px solid #fca5a5;
		}
		.live-card {
			height: 100%;
			padding: 16px;
			border-radius: 20px;
			background: rgba(255, 255, 255, 0.97);
			border: 1px solid rgba(148, 163, 184, 0.20);
			box-shadow: 0 16px 34px rgba(15, 23, 42, 0.08);
		}
		.live-card-head {
			display: flex;
			align-items: center;
			justify-content: space-between;
			gap: 12px;
			margin-bottom: 14px;
		}
		.live-card-title {
			font-size: 20px;
			font-weight: 800;
		}
		.live-card-subtitle {
			margin-top: 4px;
			font-size: 12px;
			font-weight: 700;
			color: #64748b;
			letter-spacing: 0.04em;
		}
		.live-card-stats {
			display: inline-flex;
			flex-wrap: wrap;
			gap: 8px;
		}
		.live-chip {
			display: inline-flex;
			align-items: center;
			padding: 6px 10px;
			border-radius: 999px;
			background: #e2e8f0;
			color: #334155;
			font-size: 12px;
			font-weight: 700;
		}
		.live-chip--online {
			background: #dcfce7;
			color: #166534;
		}
		.live-list {
			display: grid;
			gap: 10px;
		}
		.live-user {
			display: flex;
			align-items: center;
			justify-content: space-between;
			gap: 10px;
			padding: 14px 16px;
			border-radius: 16px;
			border: 1px solid #cbd5e1;
			transition: transform .15s ease, box-shadow .15s ease;
		}
		.live-user:hover {
			transform: translateY(-1px);
			box-shadow: 0 10px 22px rgba(15, 23, 42, 0.08);
		}
		.live-user-main {
			display: flex;
			align-items: center;
			gap: 12px;
			min-width: 0;
		}
		.live-dot {
			width: 12px;
			height: 12px;
			border-radius: 999px;
			flex: 0 0 auto;
			box-shadow: 0 0 0 4px rgba(255, 255, 255, 0.55);
		}
		.live-user-name {
			font-size: 16px;
			font-weight: 700;
			color: #0f172a;
			white-space: nowrap;
			overflow: hidden;
			text-overflow: ellipsis;
		}
		.live-user-status {
			font-size: 12px;
			font-weight: 800;
			color: #334155;
			text-transform: uppercase;
			letter-spacing: 0.04em;
			flex: 0 0 auto;
		}
		.live-empty {
			padding: 16px;
			border-radius: 16px;
			border: 1px dashed #cbd5e1;
			background: #f8fafc;
			color: #64748b;
			font-size: 13px;
			font-weight: 700;
			text-align: center;
		}
		@media (max-width: 991px) {
			.live-shell {
				padding: 16px 12px 22px !important;
			}
			.live-toolbar {
				padding: 16px;
			}
			.live-title {
				font-size: 24px;
			}
			.live-user {
				flex-direction: column;
				align-items: flex-start;
			}
		}
	</style>
</head>
<body>
<div id="app">
	<template>
		<v-container fluid class="live-shell">
			<v-row class="mb-4">
				<v-col cols="12">
					<div class="live-toolbar">
						<div>
							<div class="live-title">Живой мониторинг</div>
							<div class="live-subtitle">Операторы и очереди обновляются через PBX websocket и резервный опрос сервера.</div>
						</div>
						<div class="live-toolbar-meta">
							<span class="live-badge" :class="wsConnected ? 'live-badge--ok' : 'live-badge--warn'">
								{{ wsConnected ? 'PBX bridge online' : 'PBX bridge reconnecting' }}
							</span>
							<span class="live-badge">
								{{ lastUpdated ? 'Обновлено ' + formatDateTime(lastUpdated) : 'Загрузка...' }}
							</span>
						</div>
					</div>
					<div v-if="loadError" class="live-alert live-alert--error">{{ loadError }}</div>
					<div v-else-if="fifoWarning" class="live-alert live-alert--warning">{{ fifoWarning }}</div>
				</v-col>
			</v-row>

			<v-row>
				<v-col v-for="queue in queues" :key="queue.num" cols="12" md="4">
					<div class="live-card">
						<div class="live-card-head">
							<div>
								<div class="live-card-title">{{ queue.label }}</div>
								<div class="live-card-subtitle">FIFO {{ queue.num }}</div>
							</div>
							<div class="live-card-stats">
								<span class="live-chip">{{ queueUsers(queue).length }} опер.</span>
								<span class="live-chip live-chip--online">{{ queueOnlineCount(queue) }} online</span>
							</div>
						</div>

						<div class="live-list">
							<div
								v-for="user in queueUsers(queue)"
								:key="queue.num + '-' + user.num"
								class="live-user"
								:style="userCardStyle(user.num)"
							>
								<div class="live-user-main">
									<span class="live-dot" :style="{ background: resolveUserColor(user.num) }"></span>
									<span class="live-user-name">({{ user.num }}) {{ user.name }}</span>
								</div>
								<span class="live-user-status">{{ resolveUserStatus(user.num) }}</span>
							</div>

							<div v-if="!queueUsers(queue).length" class="live-empty">
								Операторы в этой очереди не найдены.
							</div>
						</div>
					</div>
				</v-col>
			</v-row>
		</v-container>
	</template>
</div>

<script src="/assets/other/axios.min.js"></script>
<script src="/assets/other/vue.js"></script>
<script src="/assets/other/vuetify.js"></script>

<script>
	const defaultUserState = {
		color: '#b91c1c',
		label: 'Offline',
		state: 'unregistered',
	};

	const pbxStates = {
		registered: { color: '#15803d', label: 'Online', state: 'registered' },
		register: { color: '#15803d', label: 'Online', state: 'register' },
		answered: { color: '#0f766e', label: 'В разговоре', state: 'answered' },
		ringing: { color: '#ca8a04', label: 'Звонит', state: 'ringing' },
		hangup: { color: '#15803d', label: 'Online', state: 'hangup' },
		unregister: { color: '#b91c1c', label: 'Offline', state: 'unregister' },
		unregistered: { color: '#b91c1c', label: 'Offline', state: 'unregistered' },
		pre_register: { color: '#2563eb', label: 'Подключается', state: 'pre_register' },
		register_attempt: { color: '#2563eb', label: 'Подключается', state: 'register_attempt' },
	};

	new Vue({
		el: '#app',
		vuetify: new Vuetify(),
		data: {
			queues: [
				{ num: '5201', label: 'Тех поддержка Sales Doctor', operators: [] },
				{ num: '5202', label: 'Тех поддержка Ibox', operators: [] },
				{ num: '5200', label: 'Тех поддержка IDokon', operators: [] }
			],
			users: [],
			operatorStates: {},
			lastUpdated: null,
			wsConnected: false,
			fifoWarning: '',
			loadError: '',
			liveStateVersion: 0,
			pollTimer: null,
			refreshBusy: false,
			visibilityHandler: null,
		},
		async mounted() {
			await this.refreshLiveData();
			this.startPolling();
			this.visibilityHandler = () => {
				if (!document.hidden) {
					this.refreshLiveData();
				}
			};
			document.addEventListener('visibilitychange', this.visibilityHandler);
		},
		beforeDestroy() {
			this.cleanupLive();
		},
		destroyed() {
			this.cleanupLive();
		},
		methods: {
			cleanupLive() {
				if (this.pollTimer) {
					clearInterval(this.pollTimer);
					this.pollTimer = null;
				}
				if (this.visibilityHandler) {
					document.removeEventListener('visibilitychange', this.visibilityHandler);
					this.visibilityHandler = null;
				}
			},
			formatDateTime(value) {
				const date = value instanceof Date ? value : new Date(value);
				return date.toLocaleTimeString('ru-RU', {
					hour: '2-digit',
					minute: '2-digit',
					second: '2-digit'
				});
			},
			normalizeStateName(value) {
				return String(value || '').trim().toLowerCase();
			},
			resolveStateDefinition(value) {
				const normalized = this.normalizeStateName(value);
				return pbxStates[normalized] || defaultUserState;
			},
			normalizeFifoUsers(rawUsers) {
				if (Array.isArray(rawUsers)) {
					return rawUsers
						.map((item) => String(item || '').split(':')[0].trim())
						.filter(Boolean);
				}

				if (typeof rawUsers === 'string') {
					return rawUsers
						.split(';')
						.map((item) => String(item || '').split(':')[0].trim())
						.filter(Boolean);
				}

				return [];
			},
			hexToRgba(hex, alpha) {
				const normalized = String(hex || '').replace('#', '');
				if (normalized.length !== 6) {
					return 'rgba(226, 232, 240, ' + alpha + ')';
				}

				const r = parseInt(normalized.slice(0, 2), 16);
				const g = parseInt(normalized.slice(2, 4), 16);
				const b = parseInt(normalized.slice(4, 6), 16);
				return 'rgba(' + r + ', ' + g + ', ' + b + ', ' + alpha + ')';
			},
			markAllOffline() {
				const nextState = {};
				for (const user of this.users) {
					if (!user || !user.num) {
						continue;
					}
					nextState[String(user.num)] = Object.assign({}, defaultUserState);
				}
				this.operatorStates = nextState;
			},
			applyStatus(uid, stateName) {
				if (!uid) {
					return;
				}

				const state = this.resolveStateDefinition(stateName);
				this.$set(this.operatorStates, String(uid), Object.assign({}, state));
			},
			resolveUserColor(uid) {
				const state = this.operatorStates[String(uid)];
				return state ? state.color : defaultUserState.color;
			},
			resolveUserStatus(uid) {
				const state = this.operatorStates[String(uid)];
				return state ? state.label : defaultUserState.label;
			},
			userCardStyle(uid) {
				const color = this.resolveUserColor(uid);
				return {
					borderColor: color,
					background: this.hexToRgba(color, 0.12),
				};
			},
			queueUsers(queue) {
				return this.users.filter((user) => user && user.num && queue.operators.includes(String(user.num)));
			},
			queueOnlineCount(queue) {
				return this.queueUsers(queue).filter((user) => this.resolveUserStatus(user.num) !== defaultUserState.label).length;
			},
			async refreshLiveData() {
				if (this.refreshBusy) {
					return;
				}

				this.refreshBusy = true;
				this.loadError = '';

				try {
					await Promise.all([
						this.getFifo(),
						this.getUsers(),
						this.getLiveState(),
					]);
					await this.getOperatorCondition();
				} catch (error) {
					this.loadError = 'Не удалось обновить живой мониторинг.';
					console.error('Live monitoring refresh failed', error);
				} finally {
					this.refreshBusy = false;
				}
			},
			async getUsers() {
				const response = await axios.get('/admin/monitoring/users');
				if (response.status === 200) {
					this.users = response.data.slice().sort((a, b) => a.name.localeCompare(b.name));
					if (!Object.keys(this.operatorStates).length) {
						this.markAllOffline();
					}
				}
			},
			async getFifo() {
				this.fifoWarning = '';
				const response = await axios.get('/admin/monitoring/fifo');
				if (!response.data || response.data.status !== '1' || !Array.isArray(response.data.data)) {
					this.queues = this.queues.map((queue) => Object.assign({}, queue, { operators: [] }));
					this.fifoWarning = 'Состав очереди временно недоступен.';
					return;
				}

				const remoteQueues = response.data.data;
				this.queues = this.queues.map((queue) => {
					const remoteQueue = remoteQueues.find((item) => String(item.num) === queue.num);
					return Object.assign({}, queue, {
						operators: this.normalizeFifoUsers(remoteQueue ? remoteQueue.users : []),
					});
				});
			},
			async getOperatorCondition() {
				const response = await axios.get('/admin/monitoring/liveState');
				if (response.status !== 200 || !response.data) {
					return;
				}

				this.markAllOffline();
				this.wsConnected = Boolean(response.data.bridge_connected);
				if (response.data.last_event_at) {
					this.lastUpdated = new Date(response.data.last_event_at);
				}

				try {
					const operatorStates = response.data.operators || {};
					Object.keys(operatorStates).forEach((uid) => {
						const state = operatorStates[uid];
						this.applyStatus(uid, state && state.status ? state.status : 'unregistered');
					});

					if (!this.wsConnected && Array.isArray(response.data.fallback_registered_uids)) {
						response.data.fallback_registered_uids.forEach((uid) => {
							this.applyStatus(uid, 'register');
						});
					}
				} catch (error) {
					console.error(error);
				}
			},
			async getLiveState() {
				const response = await axios.get('/admin/monitoring/liveState');
				if (response.status !== 200 || !response.data) {
					return;
				}

				const nextVersion = Number(response.data.version || 0);
				if (this.liveStateVersion === 0) {
					this.liveStateVersion = nextVersion;
				} else if (nextVersion > this.liveStateVersion) {
					this.liveStateVersion = nextVersion;
				}

				this.wsConnected = Boolean(response.data.bridge_connected);
				if (response.data.last_event_at) {
					this.lastUpdated = new Date(response.data.last_event_at);
				}
			},
			startPolling() {
				if (this.pollTimer) {
					clearInterval(this.pollTimer);
				}

				this.pollTimer = setInterval(async () => {
					if (document.hidden) {
						return;
					}

					try {
						await this.getOperatorCondition();
					} catch (error) {
						console.error('Live monitoring polling failed', error);
					}
				}, 2500);
			},
		},
	})
</script>
</body>
</html>
