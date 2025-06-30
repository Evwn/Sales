import { WebSocketServer } from 'ws';
import http from 'http';

const server = http.createServer();
const wss = new WebSocketServer({ server });

const clients = new Map();
const channels = new Map();

wss.on('connection', (ws, req) => {
  const clientId = Date.now().toString();
  clients.set(clientId, ws);
  console.log(`🔌 New client connected: ${clientId}`);

  ws.on('message', (message) => {
    console.log(`📩 Message from ${clientId}: ${message}`);
    try {
      const data = JSON.parse(message);

      if (data.event === 'pusher:subscribe') {
        const channel = data.data.channel;
        if (!channels.has(channel)) {
          channels.set(channel, new Set());
        }
        channels.get(channel).add(clientId);

        ws.send(JSON.stringify({
          event: 'pusher_internal:subscription_succeeded',
          data: {},
          channel: channel
        }));
        console.log(`✅ ${clientId} subscribed to ${channel}`);
      }

      if (data.event === 'MessageSent') {
        const channel = data.channel;
        if (channels.has(channel)) {
          channels.get(channel).forEach(clientId => {
            const client = clients.get(clientId);
            if (client && client.readyState === WebSocket.OPEN) {
              client.send(JSON.stringify({
                event: 'MessageSent',
                data: data.data,
                channel: channel
              }));
            }
          });
          console.log(`📢 Broadcasted message on ${channel} from ${clientId}`);
        }
      }

    } catch (error) {
      console.error(`❌ Error parsing message from ${clientId}:`, error);
    }
  });

  ws.on('close', () => {
    console.log(`🔌 Client ${clientId} disconnected`);
    clients.delete(clientId);

    channels.forEach((clientsInChannel, channel) => {
      clientsInChannel.delete(clientId);
      if (clientsInChannel.size === 0) {
        channels.delete(channel);
        console.log(`🧹 Channel ${channel} cleaned up`);
      }
    });
  });

  ws.on('error', (error) => {
    console.error(`❌ Client ${clientId} error:`, error);
  });
});

server.listen(6001, '127.0.0.1', () => {
  console.log('🚀 WebSocket server running on ws://127.0.0.1:6001');
});

process.on('SIGINT', () => {
  console.log('🛑 Gracefully shutting down WebSocket server...');
  wss.close(() => {
    server.close(() => {
      console.log('✅ Shutdown complete');
      process.exit(0);
    });
  });
});
