import * as THREE from './vendor/three.module.min.js';

const host = document.querySelector('[data-washer]');
if (host) initialize().catch(() => { host.dataset.renderMode = 'fallback'; window.__ready = true; });

async function initialize() {
  const reduced = window.matchMedia('(prefers-reduced-motion: reduce)');
  const canvas = document.getElementById('washer-canvas');
  const renderer = new THREE.WebGLRenderer({canvas, antialias:true, alpha:true, powerPreference:'low-power'});
  renderer.setPixelRatio(Math.min(devicePixelRatio, 1.5)); renderer.outputColorSpace = THREE.SRGBColorSpace;
  renderer.toneMapping = THREE.ACESFilmicToneMapping; renderer.toneMappingExposure = 1.15;
  renderer.shadowMap.enabled = true; renderer.shadowMap.type = THREE.PCFSoftShadowMap;
  const scene = new THREE.Scene();
  const camera = new THREE.PerspectiveCamera(36,1,.1,20); camera.position.set(.9,.95,2); camera.lookAt(0,.50,0);
  scene.add(new THREE.HemisphereLight(0xf2fff5,0x6f8878,3));
  const sun = new THREE.DirectionalLight(0xfff7e7,4); sun.position.set(-2,4,3); sun.castShadow=true;
  sun.shadow.mapSize.set(1024,1024); sun.shadow.camera.left=-1.4; sun.shadow.camera.right=1.4; sun.shadow.camera.top=1.5; sun.shadow.camera.bottom=-1.2; sun.shadow.normalBias=.015;
  scene.add(sun); const fill = new THREE.DirectionalLight(0xd7eeff,1.6); fill.position.set(3,2,-2);scene.add(fill);
  const material = (color,roughness=.45,metalness=0) => new THREE.MeshStandardMaterial({color,roughness,metalness});
  const shell=material(0xf9fbf5,.35), panel=material(0xe7eee4,.4), green=material(0x27704d,.3), rim=material(0xcbd5cd,.2,.65), dark=material(0x203f38,.26,.3);
  const machine = new THREE.Group(); scene.add(machine); machine.rotation.y=-.12;
  // Generic washer proportions in metres; this is an authored illustration, not a product model.
  function roundedBox(width,height,depth,radius,mat) {
    const x=-width/2,y=-height/2,r=radius,s=new THREE.Shape();
    s.moveTo(x+r,y);s.lineTo(x+width-r,y);s.quadraticCurveTo(x+width,y,x+width,y+r);s.lineTo(x+width,y+height-r);s.quadraticCurveTo(x+width,y+height,x+width-r,y+height);s.lineTo(x+r,y+height);s.quadraticCurveTo(x,y+height,x,y+height-r);s.lineTo(x,y+r);s.quadraticCurveTo(x,y,x+r,y);
    const geo=new THREE.ExtrudeGeometry(s,{depth,bevelEnabled:true,bevelSegments:3,steps:1,bevelSize:.009,bevelThickness:.009,curveSegments:10});geo.translate(0,0,-depth/2);
    const mesh=new THREE.Mesh(geo,mat);mesh.castShadow=true;mesh.receiveShadow=true;return mesh;
  }
  const body=roundedBox(.6,.81,.54,.025,shell);body.position.y=.47;machine.add(body);
  const top=roundedBox(.61,.035,.55,.014,shell);top.position.y=.895;machine.add(top);
  const control=roundedBox(.55,.12,.012,.012,panel);control.position.set(0,.79,.284);machine.add(control);
  const drawer=roundedBox(.18,.055,.009,.008,shell);drawer.position.set(-.145,.79,.298);machine.add(drawer);
  const drawerLine=new THREE.Mesh(new THREE.BoxGeometry(.12,.004,.005),rim);drawerLine.position.set(-.145,.788,.308);machine.add(drawerLine);
  const knob=new THREE.Mesh(new THREE.CylinderGeometry(.035,.035,.022,40),rim);knob.rotation.x=Math.PI/2;knob.position.set(.01,.79,.307);machine.add(knob);
  const screen=roundedBox(.12,.045,.009,.005,dark);screen.position.set(.155,.79,.301);machine.add(screen);
  const screenLines=new THREE.Group();for(let i=0;i<3;i++){const line=new THREE.Mesh(new THREE.BoxGeometry(.055-i*.01,.003,.002),material(0xc3e7c3));line.position.set(.147,.802-i*.011,.309);screenLines.add(line);}machine.add(screenLines);
  const recess=new THREE.Mesh(new THREE.CircleGeometry(.215,64),dark);recess.position.set(0,.43,.282);machine.add(recess);
  const drum=new THREE.Group();drum.position.set(0,.43,.29);machine.add(drum);
  const inner=new THREE.Mesh(new THREE.CircleGeometry(.177,64),material(0x375b52,.4,.6));drum.add(inner);
  for(let i=0;i<3;i++){const fin=new THREE.Mesh(new THREE.BoxGeometry(.028,.13,.024),rim);const angle=i*Math.PI*2/3;fin.position.set(Math.sin(angle)*.1,Math.cos(angle)*.1,.018);fin.rotation.z=-angle;drum.add(fin);}
  const trim=new THREE.Mesh(new THREE.TorusGeometry(.206,.025,14,80),rim);trim.position.set(0,.43,.31);machine.add(trim);
  const door=new THREE.Mesh(new THREE.TorusGeometry(.183,.026,14,80),green);door.position.set(0,.43,.335);machine.add(door);
  const glass=new THREE.Mesh(new THREE.CircleGeometry(.162,64),new THREE.MeshPhysicalMaterial({color:0x35594d,transparent:true,opacity:.48,roughness:.08,metalness:.1,clearcoat:1}));glass.position.set(0,.43,.344);machine.add(glass);
  const handle=roundedBox(.045,.1,.025,.014,dark);handle.position.set(.181,.43,.36);machine.add(handle);
  const lowerLine=new THREE.Mesh(new THREE.BoxGeometry(.51,.005,.006),panel);lowerLine.position.set(0,.17,.285);machine.add(lowerLine);
  const serviceCap=new THREE.Mesh(new THREE.CircleGeometry(.024,32),panel);serviceCap.position.set(.205,.12,.288);machine.add(serviceCap);
  for(const x of [-.235,.235])for(const z of [-.2,.2]){const foot=new THREE.Mesh(new THREE.CylinderGeometry(.026,.025,.05,18),dark);foot.position.set(x,.025,z);foot.castShadow=true;machine.add(foot);}
  const towelColors=[0xa7c8aa,0xe6c37f,0xf2eee0];
  towelColors.forEach((color,i)=>{const towel=roundedBox(.34-i*.035,.052,.27,.018,material(color,.95));towel.position.set(-.035,.952+i*.064,-.015);towel.rotation.y=i%2===0?.04:-.03;machine.add(towel);});
  const floor=new THREE.Mesh(new THREE.PlaneGeometry(5,5),new THREE.ShadowMaterial({opacity:.12}));floor.rotation.x=-Math.PI/2;floor.position.y=-.003;floor.receiveShadow=true;scene.add(floor);
  const resize=()=>{const rect=host.getBoundingClientRect();renderer.setSize(rect.width,rect.height,false);camera.aspect=rect.width/rect.height;camera.updateProjectionMatrix();render();};
  function render(){renderer.render(scene,camera);}
  let visible=true, playing=!reduced.matches, frame=0, last=0;
  const motionButton=host.querySelector('[data-motion]');
  function syncButton(){motionButton.setAttribute('aria-label',playing?'Pause illustration animation':'Play illustration animation');motionButton.setAttribute('aria-pressed',String(!playing));motionButton.querySelector('svg').innerHTML=playing?'<path d="M8 5v14M16 5v14"/>':'<path d="m8 5 11 7-11 7V5Z"/>';}
  function tick(time){frame=0;if(!visible||!playing||document.hidden)return;const delta=Math.min((time-last)/1000,.05);last=time;drum.rotation.z+=delta*.27;render();frame=requestAnimationFrame(tick);}
  function start(){cancelAnimationFrame(frame);frame=0;if(visible&&playing&&!document.hidden){last=performance.now();frame=requestAnimationFrame(tick);}else render();}
  motionButton.addEventListener('click',()=>{playing=!playing;syncButton();start();});
  host.querySelector('[data-rotate]').addEventListener('click',()=>{machine.rotation.y+=Math.PI/6;render();});
  let pointer=null, origin=0, angle=0;
  canvas.addEventListener('pointerdown',event=>{if(event.pointerType==='touch')return;pointer=event.pointerId;origin=event.clientX;angle=machine.rotation.y;canvas.setPointerCapture(event.pointerId);});
  canvas.addEventListener('pointermove',event=>{if(pointer!==event.pointerId)return;machine.rotation.y=angle+(event.clientX-origin)*.008;render();});
  canvas.addEventListener('pointerup',()=>pointer=null);canvas.addEventListener('pointercancel',()=>pointer=null);
  const observer=new IntersectionObserver(entries=>{visible=entries[0].isIntersecting;start();},{threshold:.01});observer.observe(host);
  document.addEventListener('visibilitychange',start);reduced.addEventListener('change',()=>{if(reduced.matches){playing=false;syncButton();start();}});
  canvas.addEventListener('webglcontextlost',event=>{event.preventDefault();playing=false;cancelAnimationFrame(frame);host.classList.remove('washer-ready');host.querySelector('.washer-controls').hidden=true;});
  new ResizeObserver(resize).observe(host);resize();render();host.classList.add('washer-ready');host.querySelector('.washer-controls').hidden=false;syncButton();start();
  window.__app={renderer,scene,camera};window.__ready=true;
  window.addEventListener('pageshow',event=>{if(event.persisted){resize();start();}});
  window.addEventListener('pagehide',event=>{cancelAnimationFrame(frame);if(event.persisted)return;observer.disconnect();scene.traverse(object=>{object.geometry?.dispose();if(object.material){for(const mat of Array.isArray(object.material)?object.material:[object.material])mat.dispose();}});renderer.dispose();});
}
