import { WebSocketServer } from 'ws';
import http from 'http';

const server = http.createServer();
const wss = new WebSocketServer({ server });

// Store connected clients
const clients = new Map();
const channels = new Map();

wss.on('connection', (ws, req) => {
  // Store client info
  const clientId = Date.now().toString();
  clients.set(clientId, ws);
  
  ws.on('message', (message) => {
    try {
      const data = JSON.parse(message);
      
      // Handle Pusher-compatible messages
      if (data.event === 'pusher:subscribe') {
        const channel = data.data.channel;
        if (!channels.has(channel)) {
          channels.set(channel, new Set());
        }
        channels.get(channel).add(clientId);
        
        // Send subscription success
        ws.send(JSON.stringify({
          event: 'pusher_internal:subscription_succeeded',
          data: {},
          channel: channel
        }));
      }
      
      // Handle chat messages
      if (data.event === 'MessageSent') {
        const channel = data.channel;
        if (channels.has(channel)) {
          // Broadcast to all clients in the channel
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
        }
      }
      
    } catch (error) {
      // No console.log statements in this file
    }
  });
  
  ws.on('close', () => {
    clients.delete(clientId);
    
    // Remove from all channels
    channels.forEach((clientsInChannel, channel) => {
      clientsInChannel.delete(clientId);
      if (clientsInChannel.size === 0) {
        channels.delete(channel);
      }
    });
  });
  
  ws.on('error', (error) => {
    // No console.log statements in this file
  });
});

server.listen(6001, '127.0.0.1', () => {
  // No console.log statements in this file
});

// Graceful shutdown
process.on('SIGINT', () => {
  wss.close(() => {
    server.close(() => {
      process.exit(0);
    });
  });
}); 