const Ziggy = {"url":"https:\/\/showroom-groove-ranger.ngrok-free.dev","port":null,"defaults":{},"routes":{"home":{"uri":"\/","methods":["GET","HEAD"]},"locale.switch":{"uri":"langue\/{locale}","methods":["GET","HEAD"],"parameters":["locale"]},"devis.store":{"uri":"devis","methods":["POST"]},"devis.pdf":{"uri":"devis\/pdf","methods":["POST"]}}};
if (typeof window !== 'undefined' && typeof window.Ziggy !== 'undefined') {
  Object.assign(Ziggy.routes, window.Ziggy.routes);
}
export { Ziggy };
