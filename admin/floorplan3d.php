<?php
require_once '../php/config.php';

$_sn_conn = getDBConnection();
$_sn_res  = $_sn_conn ? $_sn_conn->query("SELECT setting_key, setting_value FROM system_settings WHERE setting_key IN ('school_name','current_school_year')") : false;
$school_name = 'My School';
$current_school_year = '----';
if ($_sn_res) { while ($_sn_row = $_sn_res->fetch_assoc()) { if ($_sn_row['setting_key']=== 'school_name') $school_name=$_sn_row['setting_value']; if ($_sn_row['setting_key']=== 'current_school_year') $current_school_year=$_sn_row['setting_value']; } }
requireRole('admin');
$user_name = $_SESSION['name'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>3D Building Editor - <?= htmlspecialchars($school_name) ?></title>
<link rel="icon" type="image/jpeg" href="../images/logo2.jpg">
<link href="https://fonts.googleapis.com/css2?family=Space+Mono:wght@400;700&family=Barlow:wght@300;400;500;600;700;900&family=Barlow+Condensed:wght@500;700;900&display=swap" rel="stylesheet">
<style>

*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
  --bg:#07080c;--s0:#0c0e14;--s1:#10121a;--s2:#161921;--s3:#1c2030;
  --border:#1e2535;--border2:#26304a;
  --accent:#e8ff3c;--accent2:#ff4d6d;--accent3:#00e5ff;--accent4:#7c6fff;
  --text:#edf0fa;--muted:#3a4460;--muted2:#5a6888;
  --panel:240px;--topbar:46px;
}
body{font-family:'Barlow',sans-serif;background:var(--bg);color:var(--text);height:100vh;overflow:hidden;display:flex;flex-direction:column}

/* TOPBAR */
#topbar{height:var(--topbar);background:var(--s0);border-bottom:1px solid var(--border);display:flex;align-items:center;flex-shrink:0;z-index:200;position:relative}
.tb-logo{display:flex;align-items:center;gap:9px;padding:0 16px;height:100%;border-right:1px solid var(--border);flex-shrink:0}
.tb-logo-mark{width:24px;height:24px;background:var(--accent);border-radius:4px;display:flex;align-items:center;justify-content:center}
.tb-wordmark{font-family:'Barlow Condensed',sans-serif;font-size:16px;font-weight:900;letter-spacing:2.5px;text-transform:uppercase}
.tb-wordmark span{color:var(--accent)}
.tb-sep{width:1px;height:100%;background:var(--border);flex-shrink:0}
.tb-space{flex:1}

/* TOOLBAR BUTTONS */
.tb-btn{display:flex;align-items:center;gap:5px;height:30px;padding:0 12px;border-radius:4px;border:1px solid transparent;background:transparent;color:var(--muted2);font-family:'Barlow',sans-serif;font-size:10px;font-weight:700;cursor:pointer;transition:all .15s;text-transform:uppercase;letter-spacing:.6px;white-space:nowrap}
.tb-btn:hover{background:var(--s2);color:var(--text);border-color:var(--border)}
.tb-btn.active{background:rgba(232,255,60,.1);border-color:rgba(232,255,60,.35);color:var(--accent)}
.tb-btn.active-cyan{background:rgba(0,229,255,.1);border-color:rgba(0,229,255,.3);color:var(--accent3)}
.tb-btn.active-violet{background:rgba(124,111,255,.1);border-color:rgba(124,111,255,.35);color:var(--accent4)}
.tb-btn.active-green{background:rgba(52,211,153,.08);border-color:rgba(52,211,153,.3);color:#34d399}
.tb-btn.active-red{background:rgba(255,77,109,.08);border-color:rgba(255,77,109,.3);color:var(--accent2)}
.tb-tools{display:flex;align-items:center;height:100%;padding:0 8px;gap:3px}
.tb-actions{display:flex;align-items:center;height:100%;padding:0 10px;gap:5px;border-left:1px solid var(--border)}
.tb-ico{width:30px;height:30px;display:flex;align-items:center;justify-content:center;border-radius:4px;border:1px solid var(--border);background:transparent;color:var(--muted2);cursor:pointer;transition:all .15s}
.tb-ico:hover{background:var(--s2);color:var(--text);border-color:var(--border2)}
.tb-ico.save{border-color:rgba(0,229,255,.3);color:var(--accent3)}
.tb-ico.save:hover{background:rgba(0,229,255,.08)}

/* LAYOUT */
#layout{display:flex;flex:1;overflow:hidden}

/* LEFT PANEL */
#panel-left{width:var(--panel);background:var(--s0);border-right:1px solid var(--border);display:flex;flex-direction:column;flex-shrink:0;overflow:hidden}
.pl-sec{border-bottom:1px solid var(--border);padding:10px 12px}
.pl-label{font-family:'Space Mono',monospace;font-size:7px;font-weight:700;letter-spacing:2.5px;color:var(--muted);text-transform:uppercase;margin-bottom:7px;display:flex;align-items:center;gap:5px}
.pl-label::after{content:'';flex:1;height:1px;background:var(--border)}
.fl-list{display:flex;flex-direction:column;gap:2px;padding:6px}
.fl-row{display:flex;align-items:center;gap:8px;padding:7px 9px;border-radius:4px;border:1px solid transparent;background:transparent;cursor:pointer;transition:all .12s;text-align:left;width:100%}
.fl-row:hover{background:var(--s2);border-color:var(--border)}
.fl-row.active{background:rgba(232,255,60,.06);border-color:rgba(232,255,60,.25);color:var(--accent)}
.fl-badge{width:22px;height:22px;border-radius:3px;background:var(--s2);display:flex;align-items:center;justify-content:center;font-family:'Space Mono',monospace;font-size:8px;font-weight:700;flex-shrink:0;transition:all .12s}
.fl-row.active .fl-badge{background:var(--accent);color:#000}
.fl-name{font-size:11px;font-weight:600;flex:1}
.fl-meta{font-size:8px;color:var(--muted);font-family:'Space Mono',monospace}
.fl-btns{display:flex;gap:4px;padding:0 8px 8px}
.fl-add{flex:1;padding:6px;border-radius:4px;border:1px dashed var(--border2);background:transparent;color:var(--muted2);font-family:'Barlow',sans-serif;font-size:10px;font-weight:700;cursor:pointer;transition:all .15s;text-transform:uppercase}
.fl-add:hover{border-color:var(--accent);color:var(--accent);background:rgba(232,255,60,.04)}
.fl-del{width:30px;padding:6px;border-radius:4px;border:1px solid rgba(255,77,109,.2);background:transparent;color:#ff4d6d;font-family:'Barlow',sans-serif;font-size:11px;cursor:pointer;transition:all .15s}
.fl-del:hover{background:rgba(255,77,109,.1)}

.rm-wrap{flex:1;overflow-y:auto;padding:4px}
.rm-wrap::-webkit-scrollbar{width:3px}
.rm-wrap::-webkit-scrollbar-thumb{background:var(--border2);border-radius:99px}
.rm-item{display:flex;align-items:center;gap:7px;padding:6px 9px;border-radius:4px;cursor:pointer;border:1px solid transparent;transition:all .11s}
.rm-item:hover{background:var(--s2);border-color:var(--border)}
.rm-item.sel{background:rgba(0,229,255,.05);border-color:rgba(0,229,255,.2)}
.rm-dot{width:8px;height:8px;border-radius:2px;flex-shrink:0}
.rm-name{font-size:11px;font-weight:600;flex:1}
.rm-sz{font-size:8px;color:var(--muted);font-family:'Space Mono',monospace}

/* VIEWPORT */
#wrap{flex:1;position:relative;overflow:hidden}
#c3d{width:100%;height:100%;display:block;cursor:grab}
#c3d:active{cursor:grabbing}
#grid-canvas{position:absolute;inset:0;width:100%;height:100%;pointer-events:none;display:none;z-index:5}

/* STATUS */
#statusbar{height:26px;background:var(--s0);border-top:1px solid var(--border);display:flex;align-items:center;padding:0 14px;gap:12px;flex-shrink:0}
.sb-it{display:flex;align-items:center;gap:5px;font-family:'Space Mono',monospace;font-size:8px;color:var(--muted)}
.sb-it kbd{background:var(--s2);border:1px solid var(--border2);border-radius:2px;padding:0 4px;font-family:'Space Mono',monospace;font-size:8px}
.sb-dot{width:5px;height:5px;border-radius:50%;background:#00d464}
#statusMsg{font-family:'Space Mono',monospace;font-size:9px;color:var(--muted2);margin-left:auto}

/* FLOATING PANELS */
.fp{position:absolute;background:rgba(8,10,16,.97);backdrop-filter:blur(18px);border:1px solid var(--border2);border-radius:8px;padding:14px 16px;z-index:100;display:none;box-shadow:0 20px 60px rgba(0,0,0,.6)}
.fp.show{display:block}
.fp-title{font-family:'Barlow Condensed',sans-serif;font-size:11px;font-weight:700;letter-spacing:2px;text-transform:uppercase;margin-bottom:10px;display:flex;align-items:center;gap:7px}
.fp-close{position:absolute;top:9px;right:9px;width:19px;height:19px;border-radius:3px;background:var(--s2);border:1px solid var(--border);color:var(--muted2);font-size:10px;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all .15s}
.fp-close:hover{background:var(--border2);color:var(--text)}

/* ROOM EDITOR */
#roomEditPanel{bottom:100px;left:50%;transform:translateX(-50%);min-width:380px;border-color:rgba(0,229,255,.25)}
.re-row{display:flex;align-items:center;gap:10px;margin-bottom:9px}
.re-label{font-family:'Space Mono',monospace;font-size:8px;color:var(--muted);width:52px;flex-shrink:0}
.re-input{flex:1;background:var(--s1);border:1px solid var(--border);border-radius:3px;padding:6px 8px;color:var(--text);font-family:'Space Mono',monospace;font-size:10px;outline:none;transition:border-color .2s}
.re-input:focus{border-color:var(--accent3)}
.re-slider{flex:1;-webkit-appearance:none;height:2px;border-radius:99px;background:var(--border2);outline:none;cursor:pointer}
.re-slider::-webkit-slider-thumb{-webkit-appearance:none;width:12px;height:12px;border-radius:50%;background:var(--accent3);cursor:pointer}
.re-num{width:56px;background:var(--s1);border:1px solid var(--border);border-radius:3px;padding:4px 7px;color:var(--text);font-family:'Space Mono',monospace;font-size:10px;text-align:center;outline:none}
.re-num:focus{border-color:var(--accent3)}
.re-colors{display:flex;gap:5px;flex-wrap:wrap}
.re-swatch{width:20px;height:20px;border-radius:3px;cursor:pointer;border:2px solid transparent;transition:all .12s}
.re-swatch.sel{border-color:#fff;transform:scale(1.25)}
.re-apply{width:100%;padding:8px;border-radius:4px;border:none;background:var(--accent3);color:#000;font-family:'Barlow',sans-serif;font-size:11px;font-weight:800;cursor:pointer;margin-top:4px;text-transform:uppercase;letter-spacing:.5px;transition:opacity .15s}
.re-apply:hover{opacity:.85}

/* ROOM EDIT TOOLBAR */
#roomToolbar{position:absolute;bottom:46px;left:50%;transform:translateX(-50%);background:rgba(8,10,16,.97);border:1px solid rgba(0,229,255,.25);border-radius:50px;padding:6px 14px;display:none;gap:6px;align-items:center;backdrop-filter:blur(16px);box-shadow:0 0 24px rgba(0,229,255,.1),0 8px 28px rgba(0,0,0,.5);z-index:99;white-space:nowrap}
#roomToolbar.show{display:flex}
.rt-label{font-family:'Barlow Condensed',sans-serif;font-size:13px;font-weight:700;color:var(--accent3);letter-spacing:1px}
.rt-sep{width:1px;height:18px;background:var(--border2)}
.rt-btn{padding:4px 11px;border-radius:50px;border:1px solid var(--border2);background:var(--s1);color:var(--muted2);font-family:'Barlow',sans-serif;font-size:10px;font-weight:700;cursor:pointer;transition:all .15s;text-transform:uppercase;letter-spacing:.4px}
.rt-btn:hover{background:var(--s2);color:var(--text)}
.rt-btn.act{background:rgba(0,229,255,.1);border-color:rgba(0,229,255,.35);color:var(--accent3)}
.rt-btn.danger{color:var(--accent2);border-color:rgba(255,77,109,.25)}
.rt-btn.danger:hover{background:rgba(255,77,109,.1)}
.rt-rot{font-family:'Space Mono',monospace;font-size:9px;color:var(--muted2);min-width:36px;text-align:center}

/* DRAW GHOST */
#drawGhost{position:absolute;pointer-events:none;z-index:80;display:none;border:2px dashed var(--accent3);background:rgba(0,229,255,.05);border-radius:2px}
#measureBadge{position:absolute;top:12px;left:50%;transform:translateX(-50%);background:rgba(0,229,255,.12);border:1px solid rgba(0,229,255,.35);border-radius:4px;padding:4px 14px;font-family:'Space Mono',monospace;font-size:10px;color:var(--accent3);display:none;pointer-events:none;letter-spacing:.8px}

/* DRAW TOOLBAR */
#drawToolbar{position:absolute;bottom:46px;left:50%;transform:translateX(-50%);background:rgba(8,10,16,.97);border:1px solid rgba(52,211,153,.3);border-radius:50px;padding:6px 14px;display:none;gap:6px;align-items:center;backdrop-filter:blur(16px);z-index:99;white-space:nowrap}
#drawToolbar.show{display:flex}

/* ROOM TYPE PALETTE */
#typePalette{position:absolute;bottom:84px;left:50%;transform:translateX(-50%);background:rgba(8,10,16,.97);backdrop-filter:blur(16px);border:1px solid rgba(52,211,153,.25);border-radius:10px;padding:10px;display:none;gap:5px;flex-wrap:wrap;max-width:440px;justify-content:center;z-index:95}
#typePalette.show{display:flex}
.tp-item{display:flex;align-items:center;gap:6px;padding:6px 10px;border-radius:6px;border:1px solid var(--border);background:var(--s1);cursor:pointer;transition:all .13s;font-size:10px;font-weight:700;color:var(--muted2)}
.tp-item:hover{background:var(--s2);color:var(--text);border-color:var(--border2)}
.tp-item.sel{color:var(--text);border-color:var(--border2);background:var(--s2)}
.tp-dot{width:9px;height:9px;border-radius:2px;flex-shrink:0}

/* GRID CONTROLS */
#gridControls{display:none;position:absolute;bottom:50px;right:14px;background:rgba(8,10,16,.94);border:1px solid rgba(0,229,255,.2);border-radius:8px;padding:12px 14px;z-index:20;min-width:170px;backdrop-filter:blur(12px)}
.gc-row{display:flex;align-items:center;gap:8px;margin-bottom:8px}
.gc-label{font-family:'Space Mono',monospace;font-size:8px;color:var(--muted);width:45px}
.gc-sel{flex:1;background:var(--s1);border:1px solid var(--border);border-radius:3px;padding:4px 6px;color:var(--text);font-family:'Space Mono',monospace;font-size:8px;outline:none}
#cursorPos{font-family:'Space Mono',monospace;font-size:8px;color:var(--accent3)}

/* OVERLAP REPORT PANEL */
#overlapPanel{position:absolute;top:54px;right:12px;background:rgba(8,10,16,.97);backdrop-filter:blur(18px);border:1px solid rgba(255,77,109,.4);border-radius:8px;padding:14px 16px;z-index:100;display:none;min-width:240px;max-width:300px;box-shadow:0 20px 60px rgba(0,0,0,.6)}
#overlapPanel .fp-title{color:var(--accent2)}
.ov-item{display:flex;align-items:flex-start;gap:7px;padding:6px 8px;border-radius:4px;border:1px solid rgba(255,77,109,.18);background:rgba(255,77,109,.05);margin-bottom:4px;font-size:9px;font-family:'Space Mono',monospace;color:var(--muted2);line-height:1.6;cursor:pointer;transition:background .12s}
.ov-item:hover{background:rgba(255,77,109,.12)}
.ov-dot{width:7px;height:7px;border-radius:50%;background:var(--accent2);flex-shrink:0;margin-top:3px}
.ov-ok{padding:8px;text-align:center;font-size:9px;font-family:'Space Mono',monospace;color:#34d399}

/* TOAST */
#toast{position:absolute;top:12px;right:14px;background:rgba(0,229,255,.1);border:1px solid rgba(0,229,255,.35);border-radius:50px;padding:5px 14px;font-family:'Barlow',sans-serif;font-size:11px;font-weight:700;color:var(--accent3);display:none;pointer-events:none;animation:fadeInOut 2s ease forwards}
@keyframes fadeInOut{0%{opacity:0;transform:translateY(-4px)}15%{opacity:1;transform:translateY(0)}75%{opacity:1}100%{opacity:0}}

/* HINT */
#hint{position:absolute;bottom:34px;left:14px;font-family:'Space Mono',monospace;font-size:8px;color:var(--muted);line-height:2}
#hint kbd{background:var(--s2);border:1px solid var(--border2);border-radius:2px;padding:1px 4px;font-family:'Space Mono',monospace;font-size:7px;color:var(--muted2)}

/* EMPTY STATE */
#emptyState{position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);text-align:center;pointer-events:none;z-index:10}
.es-icon{font-size:52px;margin-bottom:12px;opacity:.25}
.es-title{font-family:'Barlow Condensed',sans-serif;font-size:22px;font-weight:700;letter-spacing:2px;color:var(--muted2);text-transform:uppercase;margin-bottom:6px}
.es-sub{font-family:'Space Mono',monospace;font-size:9px;color:var(--muted);line-height:2}

/* MODAL */
#modalOverlay{position:fixed;inset:0;background:rgba(0,0,0,.7);backdrop-filter:blur(6px);z-index:9998;display:none;align-items:center;justify-content:center}
#modalOverlay.show{display:flex}
.modal-box{background:var(--s1);border:1px solid var(--border2);border-radius:10px;padding:22px 24px;min-width:320px;max-width:400px;box-shadow:0 20px 60px rgba(0,0,0,.6);position:relative}
.modal-title{font-family:'Barlow Condensed',sans-serif;font-size:15px;font-weight:700;letter-spacing:2px;text-transform:uppercase;margin-bottom:16px}
.modal-close{position:absolute;top:10px;right:10px;width:22px;height:22px;border-radius:3px;border:1px solid var(--border);background:var(--s2);color:var(--muted2);cursor:pointer;font-size:11px;display:flex;align-items:center;justify-content:center;transition:all .15s}
.modal-close:hover{background:var(--border2);color:var(--text)}
.m-row{margin-bottom:10px}
.m-label{font-family:'Space Mono',monospace;font-size:8px;color:var(--muted);display:block;margin-bottom:5px;text-transform:uppercase;letter-spacing:1.5px}
.m-input{width:100%;background:var(--s2);border:1px solid var(--border2);border-radius:4px;padding:7px 10px;color:var(--text);font-family:'Barlow',sans-serif;font-size:12px;outline:none;transition:border-color .2s}
.m-input:focus{border-color:var(--accent3)}
.m-grid{display:grid;grid-template-columns:1fr 1fr;gap:8px}
.m-btn{width:100%;padding:10px;border-radius:4px;border:none;background:var(--accent3);color:#000;font-family:'Barlow',sans-serif;font-size:12px;font-weight:800;cursor:pointer;text-transform:uppercase;letter-spacing:.8px;margin-top:6px;transition:opacity .15s}
.m-btn:hover{opacity:.85}
.m-presets{display:grid;grid-template-columns:1fr 1fr;gap:4px;margin-top:6px}
.m-preset{padding:5px;border-radius:3px;border:1px solid var(--border);background:var(--s2);color:var(--muted2);font-family:'Space Mono',monospace;font-size:8px;cursor:pointer;transition:all .12s;text-align:center}
.m-preset:hover{border-color:var(--border2);color:var(--text);background:var(--s3)}

/* COLOR SWATCHES in modal */
.swatch-row{display:flex;gap:5px;flex-wrap:wrap}
.m-swatch{width:22px;height:22px;border-radius:3px;cursor:pointer;border:2px solid transparent;transition:all .12s;flex-shrink:0}
.m-swatch.sel{border-color:#fff;transform:scale(1.2)}

/* SAVE/LOAD panel */
#dbPanel{position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);width:440px;max-height:80vh;overflow-y:auto;border-color:rgba(52,211,153,.25)}
#dbPanel .fp-title{color:#34d399}
.db-list{border:1px solid var(--border);border-radius:5px;max-height:220px;overflow-y:auto}
.db-list::-webkit-scrollbar{width:3px}
.db-list::-webkit-scrollbar-thumb{background:var(--border2)}
.db-item{display:flex;align-items:center;gap:10px;padding:10px 12px;border-bottom:1px solid var(--border);transition:background .12s;cursor:pointer}
.db-item:last-child{border-bottom:none}
.db-item:hover{background:var(--s1)}
.db-item-name{font-size:12px;font-weight:700;flex:1}
.db-item-meta{font-size:8px;color:var(--muted);font-family:'Space Mono',monospace;margin-top:2px}
.db-act-btn{padding:3px 9px;border-radius:3px;font-family:'Barlow',sans-serif;font-size:9px;font-weight:700;cursor:pointer;border:none;text-transform:uppercase}
.db-load{background:rgba(52,211,153,.12);color:#34d399}
.db-load:hover{background:rgba(52,211,153,.25)}
.db-del{background:rgba(255,77,109,.1);color:var(--accent2)}
.db-del:hover{background:rgba(255,77,109,.2)}
.db-empty{text-align:center;padding:20px;color:var(--muted);font-size:10px;font-family:'Space Mono',monospace}
.db-name-row{display:flex;gap:7px;margin-bottom:12px}
.db-name-input{flex:1;background:var(--s1);border:1px solid var(--border);border-radius:4px;padding:7px 10px;color:var(--text);font-family:'Space Mono',monospace;font-size:11px;outline:none;transition:border-color .2s}
.db-name-input:focus{border-color:#34d399}
.db-save-btn{padding:7px 14px;border-radius:4px;border:none;background:var(--accent);color:#000;font-family:'Barlow',sans-serif;font-size:10px;font-weight:700;cursor:pointer;text-transform:uppercase}
.db-save-btn:hover{opacity:.85}
.db-io{display:flex;gap:6px;margin-top:8px}
.db-io-btn{flex:1;padding:7px;border-radius:4px;border:1px solid var(--border2);background:var(--s2);color:var(--muted2);font-family:'Barlow',sans-serif;font-size:10px;font-weight:700;cursor:pointer;text-transform:uppercase;transition:all .15s}
.db-io-btn:hover{background:var(--s3);color:var(--text)}
#dbBackdrop{position:absolute;inset:0;background:rgba(0,0,0,.5);backdrop-filter:blur(3px);display:none;z-index:250}
#dbBackdrop.show{display:block}
#dbPanel{z-index:300}

/* FLOOR POSITION POPUP */
#floorPosPanel{bottom:auto;top:14px;right:14px;left:auto;transform:none;min-width:260px;border-color:rgba(232,255,60,.25);animation:none}
#floorPosPanel .fp-title{color:var(--accent)}

/* DOOR PANEL */
#doorPanel{top:14px;left:50%;transform:translateX(-50%);min-width:360px;border-color:rgba(251,146,60,.3);animation:dropIn .25s cubic-bezier(.22,1,.36,1)}
@keyframes dropIn{from{transform:translateX(-50%) translateY(-8px);opacity:0}to{transform:translateX(-50%) translateY(0);opacity:1}}
#doorPanel .fp-title{color:#fb923c}

/* WINDOW PANEL */
#winPanel{top:14px;left:50%;transform:translateX(-50%);min-width:380px;border-color:rgba(0,229,255,.3);animation:dropIn .25s cubic-bezier(.22,1,.36,1)}
#winPanel .fp-title{color:var(--accent3)}

/* SLAB PANEL */
#slabPanel{bottom:100px;left:50%;transform:translateX(-50%);min-width:360px;border-color:rgba(167,139,250,.35)}
#slabPanel .fp-title{color:#a78bfa}
#slabToolbar{position:absolute;bottom:46px;left:50%;transform:translateX(-50%);background:rgba(8,10,16,.97);border:1px solid rgba(167,139,250,.35);border-radius:50px;padding:6px 14px;display:none;gap:6px;align-items:center;backdrop-filter:blur(16px);z-index:99;white-space:nowrap}
#slabToolbar.show{display:flex}
.sl-label{font-family:'Barlow Condensed',sans-serif;font-size:13px;font-weight:700;color:#a78bfa;letter-spacing:1px}

/* shared side picker */
.side-row{display:flex;gap:5px;flex-wrap:wrap;margin-bottom:8px}
.side-btn{padding:4px 11px;border-radius:50px;border:1px solid var(--border);background:var(--s1);color:var(--muted2);font-family:'Barlow',sans-serif;font-size:10px;font-weight:700;cursor:pointer;transition:all .12s}
.side-btn:hover{background:var(--s2);color:var(--text)}
.side-btn.sel{background:rgba(251,146,60,.12);border-color:rgba(251,146,60,.45);color:#fb923c}
.side-btn.sel-cyan{background:rgba(0,229,255,.1);border-color:rgba(0,229,255,.4);color:var(--accent3)}
.dp-row{display:flex;align-items:center;gap:10px;margin-bottom:8px}
.dp-label{font-family:'Space Mono',monospace;font-size:8px;color:var(--muted);width:55px;flex-shrink:0}
.dp-slider{flex:1;-webkit-appearance:none;height:2px;border-radius:99px;background:var(--border2);outline:none;cursor:pointer}
.dp-slider::-webkit-slider-thumb{-webkit-appearance:none;width:12px;height:12px;border-radius:50%;background:#fb923c;cursor:pointer}
.dp-slider-cyan::-webkit-slider-thumb{background:var(--accent3)!important}
.dp-slider-violet::-webkit-slider-thumb{background:#a78bfa!important}
.dp-num{width:52px;background:var(--s1);border:1px solid var(--border);border-radius:3px;padding:4px 7px;color:var(--text);font-family:'Space Mono',monospace;font-size:10px;text-align:center;outline:none}
.dp-num:focus{border-color:#fb923c}
.dp-list{max-height:110px;overflow-y:auto;margin-bottom:9px}
.dp-list::-webkit-scrollbar{width:3px}
.dp-list::-webkit-scrollbar-thumb{background:var(--border)}
.dp-list-item{display:flex;align-items:center;gap:8px;padding:5px 8px;border-radius:4px;border:1px solid var(--border);margin-bottom:3px;font-size:9px;font-family:'Space Mono',monospace;color:var(--muted2);cursor:pointer;transition:background .1s}
.dp-list-item:hover{background:var(--s2)}
.dp-list-item.sel-item{background:rgba(0,229,255,.05);border-color:rgba(0,229,255,.2)}
.dp-del{margin-left:auto;background:rgba(255,77,109,.1);border:1px solid rgba(255,77,109,.25);border-radius:3px;color:var(--accent2);font-size:8px;padding:2px 7px;cursor:pointer;font-family:'Barlow',sans-serif;font-weight:700;text-transform:uppercase}
.dp-del:hover{background:rgba(255,77,109,.22)}
.dp-add{width:100%;padding:8px;border-radius:4px;border:none;font-family:'Barlow',sans-serif;font-size:11px;font-weight:800;cursor:pointer;text-transform:uppercase;letter-spacing:.5px;transition:opacity .15s;margin-top:2px}
.dp-add:hover{opacity:.85}
.dp-empty{text-align:center;color:var(--muted);font-size:9px;font-family:'Space Mono',monospace;padding:10px 0}

/* School system integration overrides */
#school-topbar-badge {
  display:flex;align-items:center;gap:8px;padding:0 14px;border-left:1px solid var(--border);height:100%;
  font-family:'Barlow',sans-serif;font-size:11px;color:var(--muted2);
}
#school-topbar-badge a {
  color:var(--muted2);text-decoration:none;padding:3px 8px;border-radius:3px;border:1px solid var(--border);
  font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;transition:all .15s;
}
#school-topbar-badge a:hover { background:var(--s2);color:var(--text); }
</style>
</head>
<body>


<!-- TOPBAR -->
<div id="topbar">
  <div class="tb-logo">
    <div class="tb-logo-mark">
      <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
        <rect x="1" y="5" width="12" height="8" rx="1" stroke="#000" stroke-width="1.5"/>
        <path d="M4 5V3.5a3 3 0 016 0V5" stroke="#000" stroke-width="1.5"/>
      </svg>
    </div>
    <div class="tb-wordmark">ARCH<span>FORM</span></div>
  </div>

  <!-- VIEW BUTTONS -->
  <div class="tb-tools" style="border-right:1px solid var(--border)">
    <button class="tb-btn active" id="btn3d" onclick="setView('3d')">
      <svg width="11" height="11" viewBox="0 0 11 11" fill="none"><rect x="1" y="3.5" width="9" height="6.5" rx=".8" stroke="currentColor" stroke-width="1.2"/><path d="M3.5 3.5V2.5a2 2 0 015 0v1" stroke="currentColor" stroke-width="1.2"/></svg>3D View
    </button>
    <button class="tb-btn" id="btnFloor" onclick="setView('floor')">
      <svg width="11" height="11" viewBox="0 0 11 11" fill="none"><rect x="1" y="1" width="9" height="9" rx=".8" stroke="currentColor" stroke-width="1.2"/><path d="M1 5.5h9M5.5 1v9" stroke="currentColor" stroke-width="1"/></svg>Floor Plan
    </button>
    <button class="tb-btn" id="btnGrid" onclick="setView('grid')">
      <svg width="11" height="11" viewBox="0 0 11 11" fill="none"><path d="M1 4h9M1 7h9M4 1v9M7 1v9" stroke="currentColor" stroke-width="1.1" opacity=".9"/><rect x="1" y="1" width="9" height="9" rx=".8" stroke="currentColor" stroke-width="1.2"/></svg>Grid
    </button>
  </div>

  <!-- TOOL BUTTONS -->
  <div class="tb-tools">
    <button class="tb-btn" id="btnDraw" onclick="toggleDraw()">
      <svg width="11" height="11" viewBox="0 0 11 11" fill="none"><rect x="1" y="1" width="6" height="6" rx=".8" stroke="currentColor" stroke-width="1.2" stroke-dasharray="2 1"/><path d="M8 5v5M5.5 7.5h5" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/></svg>Draw Room
    </button>
    <button class="tb-btn" id="btnMoveMode" onclick="toggleMoveMode()">
      <svg width="11" height="11" viewBox="0 0 11 11" fill="none"><path d="M5.5 1.5v8M1.5 5.5h8" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/><path d="M5.5 1.5L4 3M5.5 1.5L7 3M5.5 9.5L4 8M5.5 9.5L7 8M1.5 5.5L3 4M1.5 5.5L3 7M9.5 5.5L8 4M9.5 5.5L8 7" stroke="currentColor" stroke-width="1.1" stroke-linecap="round"/></svg>Move
    </button>
    <button class="tb-btn" id="btnSelectMode" onclick="toggleSelectMode()">
      <svg width="11" height="11" viewBox="0 0 11 11" fill="none"><path d="M2 2l6 3.5-3 .5L3.5 9 2 2z" stroke="currentColor" stroke-width="1.2" stroke-linejoin="round"/></svg>Select
    </button>
  </div>

  <div class="tb-sep"></div>

  <div class="tb-tools">
    <button class="tb-btn" id="btnDoors" onclick="toggleDoorPanel()" title="Door Editor">
      <svg width="11" height="11" viewBox="0 0 11 11" fill="none"><rect x="1.5" y="1" width="8" height="9.5" rx=".8" stroke="currentColor" stroke-width="1.2"/><circle cx="8" cy="5.5" r=".9" fill="currentColor"/></svg>Doors
    </button>
    <button class="tb-btn" id="btnWindows" onclick="toggleWinPanel()" title="Window Editor">
      <svg width="11" height="11" viewBox="0 0 11 11" fill="none"><rect x="1" y="1" width="9" height="9" rx=".8" stroke="currentColor" stroke-width="1.2"/><path d="M1 5.5h9M5.5 1v9" stroke="currentColor" stroke-width="1.1" opacity=".6"/></svg>Windows
    </button>
    <button class="tb-btn" id="btnSlab" onclick="toggleSlabMode()" title="Floor Slab">
      <svg width="11" height="11" viewBox="0 0 11 11" fill="none"><rect x="1" y="4" width="9" height="3" rx=".5" stroke="currentColor" stroke-width="1.2"/><path d="M3 4V2.5M5.5 4V2M8 4V2.5" stroke="currentColor" stroke-width="1.1" stroke-linecap="round" opacity=".6"/></svg>Slab
    </button>
    <button class="tb-btn" id="btnStairs" onclick="openStairsModal()" title="Add Staircase">
      <svg width="11" height="11" viewBox="0 0 11 11" fill="none"><path d="M1 9h2V7h2V5h2V3h3" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/></svg>Stairs
    </button>
  </div>

  <div class="tb-sep"></div>
  <div class="tb-space"></div>
    <div id="school-topbar-badge"><span>Logged in as <strong style="color:var(--text)"><?= htmlspecialchars($user_name) ?></strong></span><a href="dashboard.php">← Admin Dashboard</a></div>

  <div class="tb-actions">
    <button class="tb-ico" onclick="undoAction()" title="Undo (Ctrl+Z)" id="btnUndo" style="opacity:.4">
      <svg width="13" height="13" viewBox="0 0 13 13" fill="none"><path d="M2 5.5A4 4 0 1110 9" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/><path d="M2 2.5v3h3" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/></svg>
    </button>
    <button class="tb-ico" onclick="redoAction()" title="Redo (Ctrl+Y)" id="btnRedo" style="opacity:.4">
      <svg width="13" height="13" viewBox="0 0 13 13" fill="none"><path d="M11 5.5A4 4 0 103 9" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/><path d="M11 2.5v3H8" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/></svg>
    </button>
    <button class="tb-ico" onclick="takeScreenshot()" title="Screenshot">
      <svg width="13" height="13" viewBox="0 0 13 13" fill="none"><rect x="1" y="3" width="11" height="8.5" rx="1" stroke="currentColor" stroke-width="1.2"/><circle cx="6.5" cy="7.2" r="2" stroke="currentColor" stroke-width="1.2"/><path d="M4.5 3l.8-1.5h2.4L8.5 3" stroke="currentColor" stroke-width="1.1" stroke-linecap="round" stroke-linejoin="round"/></svg>
    </button>
    <button class="tb-ico" onclick="toggleStatsPanel()" title="Floor Stats" id="btnStats">
      <svg width="13" height="13" viewBox="0 0 13 13" fill="none"><rect x="1" y="1" width="11" height="11" rx="1.2" stroke="currentColor" stroke-width="1.2"/><path d="M3.5 9V6.5M6.5 9V4M9.5 9V7" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/></svg>
    </button>
    <button class="tb-ico" onclick="toggleOverlapPanel()" title="Check Room Overlaps" id="btnOverlap">
      <svg width="13" height="13" viewBox="0 0 13 13" fill="none"><rect x="1.5" y="1.5" width="6" height="6" rx=".8" stroke="currentColor" stroke-width="1.2"/><rect x="5.5" y="5.5" width="6" height="6" rx=".8" stroke="currentColor" stroke-width="1.2"/><path d="M5.5 7.5h2v-2" stroke="currentColor" stroke-width="1.1" stroke-linecap="round" stroke-linejoin="round" opacity=".6"/></svg>
    </button>
    <button class="tb-ico" onclick="resetView()" title="Reset View">
      <svg width="13" height="13" viewBox="0 0 13 13" fill="none"><path d="M2.5 6.5A4 4 0 1110 4M10 2v2.5H7.5" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/></svg>
    </button>
    <button class="tb-ico" onclick="topDown()" title="Top View">
      <svg width="13" height="13" viewBox="0 0 13 13" fill="none"><rect x="1" y="1" width="11" height="11" rx="1.5" stroke="currentColor" stroke-width="1.3"/></svg>
    </button>
    <button class="tb-ico save" onclick="openSaveLoad()" title="Save / Load">
      <svg width="13" height="13" viewBox="0 0 13 13" fill="none"><path d="M2 2.5A.5.5 0 012.5 2h7l1.5 1.5V11a.5.5 0 01-.5.5H2.5A.5.5 0 012 11V2.5z" stroke="currentColor" stroke-width="1.3"/><rect x="4" y="7" width="5" height="4.5" rx=".5" stroke="currentColor" stroke-width="1.1"/><path d="M4.5 2v3h4V2" stroke="currentColor" stroke-width="1.1"/></svg>
    </button>
  </div>
</div>

<!-- LAYOUT -->
<div id="layout">
  <!-- LEFT PANEL -->
  <div id="panel-left">
    <!-- BUILDING DIMENSIONS -->
    <div class="pl-sec" style="padding-bottom:8px">
      <div class="pl-label">Building</div>
      <div style="display:flex;flex-direction:column;gap:5px">
        <div style="display:flex;align-items:center;gap:6px">
          <span style="font-family:'Space Mono',monospace;font-size:7px;color:var(--muted);width:42px;flex-shrink:0">WIDTH</span>
          <input type="number" id="bwInput" min="100" max="3000" step="50" value="800"
            style="flex:1;background:var(--s2);border:1px solid var(--border);border-radius:3px;padding:4px 6px;color:var(--text);font-family:'Space Mono',monospace;font-size:9px;outline:none"
            onchange="applyBuildingDims()">
          <span style="font-family:'Space Mono',monospace;font-size:7px;color:var(--muted)">px</span>
        </div>
        <div style="display:flex;align-items:center;gap:6px">
          <span style="font-family:'Space Mono',monospace;font-size:7px;color:var(--muted);width:42px;flex-shrink:0">DEPTH</span>
          <input type="number" id="bdInput" min="100" max="3000" step="50" value="600"
            style="flex:1;background:var(--s2);border:1px solid var(--border);border-radius:3px;padding:4px 6px;color:var(--text);font-family:'Space Mono',monospace;font-size:9px;outline:none"
            onchange="applyBuildingDims()">
          <span style="font-family:'Space Mono',monospace;font-size:7px;color:var(--muted)">px</span>
        </div>
        <div style="display:flex;align-items:center;gap:6px">
          <span style="font-family:'Space Mono',monospace;font-size:7px;color:var(--muted);width:42px;flex-shrink:0">FL HT</span>
          <input type="number" id="fhInput" min="40" max="300" step="10" value="90"
            style="flex:1;background:var(--s2);border:1px solid var(--border);border-radius:3px;padding:4px 6px;color:var(--text);font-family:'Space Mono',monospace;font-size:9px;outline:none"
            onchange="applyBuildingDims()">
          <span style="font-family:'Space Mono',monospace;font-size:7px;color:var(--muted)">px</span>
        </div>
        <button onclick="applyBuildingDims(true)" style="width:100%;padding:5px;border-radius:3px;border:none;background:rgba(232,255,60,.12);border:1px solid rgba(232,255,60,.25);color:var(--accent);font-family:'Barlow',sans-serif;font-size:9px;font-weight:700;cursor:pointer;text-transform:uppercase;letter-spacing:.5px;transition:opacity .15s">↺ Apply & Rebuild</button>
      </div>
    </div>
    <div class="pl-sec" style="padding-bottom:0;border-top:1px solid var(--border)">
      <div class="pl-label">Floors</div>
    </div>
    <div class="fl-list" id="floorList"></div>
    <div class="fl-btns">
      <button class="fl-add" onclick="addFloor()">＋ Add Floor</button>
      <button class="fl-del" onclick="removeTopFloor()" title="Remove top floor">✕</button>
    </div>
    <div class="pl-sec" style="padding-bottom:4px;border-top:1px solid var(--border)">
      <div class="pl-label">Rooms</div>
    </div>
    <div class="rm-wrap" id="roomList"></div>
  </div>

  <!-- VIEWPORT -->
  <div id="wrap">
    <canvas id="c3d"></canvas>
    <canvas id="grid-canvas"></canvas>

    <!-- EMPTY STATE -->
    <div id="emptyState">
      <div class="es-icon">🏗️</div>
      <div class="es-title">No Rooms Yet</div>
      <div class="es-sub">Click <kbd style="background:var(--s2);border:1px solid var(--border2);border-radius:2px;padding:1px 5px;font-family:'Space Mono',monospace;font-size:9px">Draw Room</kbd> then drag on canvas<br>to place your first room</div>
    </div>

    <!-- DRAW TOOLBAR -->
    <div id="drawToolbar">
      <span style="font-family:'Barlow Condensed',sans-serif;font-size:12px;font-weight:700;color:#34d399;letter-spacing:1px">DRAW MODE</span>
      <div class="rt-sep"></div>
      <span style="font-family:'Space Mono',monospace;font-size:9px;color:var(--muted2)" id="drawTypeLabel">Living Room</span>
      <div class="rt-sep"></div>
      <button class="rt-btn" onclick="toggleDraw()" style="color:var(--accent2);border-color:rgba(255,77,109,.25)">✕ Exit</button>
    </div>

    <!-- ROOM TYPE PALETTE -->
    <div id="typePalette"></div>

    <!-- ROOM TOOLBAR -->
    <div id="roomToolbar">
      <span class="rt-label" id="rtLabel">Room</span>
      <div class="rt-sep"></div>
      <button class="rt-btn" id="rtMove" onclick="setRoomTool('move')">✋ Move</button>
      <button class="rt-btn" onclick="rotateRoom(-90)">↺ −90°</button>
      <button class="rt-btn" onclick="rotateRoom(45)">↻ +45°</button>
      <span class="rt-rot" id="rtRot">0°</span>
      <div class="rt-sep"></div>
      <button class="rt-btn" onclick="duplicateRoom()">⧉ Dupe</button>
      <button class="rt-btn" onclick="openRoomEditPanel()">Edit</button>
      <div class="rt-sep"></div>
      <button class="rt-btn danger" onclick="deleteSelRoom()">✕ Del</button>
    </div>

    <!-- STAIR TOOLBAR -->
    <div id="stairToolbar" style="position:absolute;bottom:46px;left:50%;transform:translateX(-50%);background:rgba(8,10,16,.97);border:1px solid rgba(234,179,8,.3);border-radius:50px;padding:6px 14px;display:none;gap:6px;align-items:center;backdrop-filter:blur(16px);box-shadow:0 0 24px rgba(234,179,8,.12),0 8px 28px rgba(0,0,0,.5);z-index:99;white-space:nowrap">
      <span style="font-family:'Barlow Condensed',sans-serif;font-size:13px;font-weight:700;color:#eab308;letter-spacing:1px" id="stairTbLabel">🪜 Stair</span>
      <div class="rt-sep"></div>
      <button class="rt-btn" onclick="rotateStair(-90)">↺ −90°</button>
      <button class="rt-btn" onclick="rotateStair(-45)">↺ −45°</button>
      <button class="rt-btn" onclick="rotateStair(45)">↻ +45°</button>
      <button class="rt-btn" onclick="rotateStair(90)">↻ +90°</button>
      <span class="rt-rot" id="stairTbRot">0°</span>
      <div class="rt-sep"></div>
      <button class="rt-btn" onclick="openStairEditPanel()" style="color:#eab308;border-color:rgba(234,179,8,.35)">✎ Edit</button>
      <div class="rt-sep"></div>
      <button class="rt-btn danger" onclick="deleteSelStair()">✕ Del</button>
    </div>

    <!-- STAIR EDIT PANEL -->
    <div class="fp" id="stairEditPanel" style="bottom:100px;left:50%;transform:translateX(-50%);min-width:360px;border-color:rgba(234,179,8,.35)">
      <button class="fp-close" onclick="closeStairEditPanel()">✕</button>
      <div class="fp-title" style="color:#eab308">🪜 Edit Staircase — <span id="sepName" style="font-weight:400;color:var(--text);letter-spacing:0"></span></div>
      <div class="re-row">
        <span class="re-label">Name</span>
        <input class="re-input" id="sepNameInput" type="text" placeholder="Staircase name">
      </div>
      <div class="re-row">
        <span class="re-label">Width</span>
        <input type="range" class="re-slider" id="sepW" min="20" max="300" value="60" style="accent-color:#eab308" oninput="document.getElementById('sepWn').value=this.value">
        <input type="number" class="re-num" id="sepWn" value="60" oninput="document.getElementById('sepW').value=this.value">
      </div>
      <div class="re-row">
        <span class="re-label">Depth</span>
        <input type="range" class="re-slider" id="sepD" min="30" max="500" value="120" style="accent-color:#eab308" oninput="document.getElementById('sepDn').value=this.value">
        <input type="number" class="re-num" id="sepDn" value="120" oninput="document.getElementById('sepD').value=this.value">
      </div>
      <div class="re-row">
        <span class="re-label">Height</span>
        <input type="range" class="re-slider" id="sepH" min="10" max="300" value="90" style="accent-color:#eab308" oninput="document.getElementById('sepHn').value=this.value">
        <input type="number" class="re-num" id="sepHn" value="90" oninput="document.getElementById('sepH').value=this.value">
      </div>
      <div class="re-row">
        <span class="re-label">Length</span>
        <input type="range" class="re-slider" id="sepL" min="30" max="500" value="120" style="accent-color:#eab308" oninput="document.getElementById('sepLn').value=this.value">
        <input type="number" class="re-num" id="sepLn" value="120" oninput="document.getElementById('sepL').value=this.value">
      </div>
      <div class="re-row" style="margin-bottom:4px">
        <span class="re-label">Style</span>
        <div style="display:flex;gap:4px;flex-wrap:wrap">
          <button class="side-btn sel" id="sepStyleStraight" onclick="sepSelStyle('straight')">Straight</button>
          <button class="side-btn" id="sepStyleL" onclick="sepSelStyle('L')">L-Shape</button>
          <button class="side-btn" id="sepStyleU" onclick="sepSelStyle('U')">U-Shape</button>
          <button class="side-btn" id="sepStyleCurve" onclick="sepSelStyle('curve')">Curved</button>
          <button class="side-btn" id="sepStyleSpiral" onclick="sepSelStyle('spiral')">Spiral</button>
        </div>
      </div>
      <button class="re-apply" style="background:#eab308" onclick="applyStairEdit()">✓ Apply Changes</button>
    </div>

    <!-- ROOM EDIT PANEL -->
    <div class="fp" id="roomEditPanel" style="bottom:100px;left:50%;transform:translateX(-50%);min-width:380px;border-color:rgba(0,229,255,.25)">
      <button class="fp-close" onclick="closeRoomEditPanel()">✕</button>
      <div class="fp-title" style="color:var(--accent3)">Edit Room — <span id="repName" style="font-weight:400;color:var(--text);letter-spacing:0"></span></div>
      <div class="re-row">
        <span class="re-label">Name</span>
        <input class="re-input" id="repNameInput" type="text" placeholder="Room name">
      </div>
      <div class="re-row">
        <span class="re-label">Width</span>
        <input type="range" class="re-slider" id="repW" min="20" max="800" value="120" oninput="document.getElementById('repWn').value=this.value">
        <input type="number" class="re-num" id="repWn" min="20" value="120" oninput="document.getElementById('repW').value=this.value">
      </div>
      <div class="re-row">
        <span class="re-label">Depth</span>
        <input type="range" class="re-slider" id="repD" min="20" max="680" value="100" oninput="document.getElementById('repDn').value=this.value">
        <input type="number" class="re-num" id="repDn" min="20" value="100" oninput="document.getElementById('repD').value=this.value">
      </div>
      <div class="re-row">
        <span class="re-label">Height</span>
        <input type="range" class="re-slider" id="repH" min="20" max="200" value="60" oninput="document.getElementById('repHn').value=this.value">
        <input type="number" class="re-num" id="repHn" min="20" value="60" oninput="document.getElementById('repH').value=this.value">
      </div>
      <div class="re-row">
        <span class="re-label">Color</span>
        <div class="re-colors" id="repColors"></div>
      </div>
      <div class="re-row" id="repLRow" style="display:none">
        <span class="re-label" style="font-size:7px">Notch W%</span>
        <input type="range" class="re-slider" id="repLW" min="10" max="80" value="50" oninput="document.getElementById('repLWn').value=this.value">
        <input type="number" class="re-num" id="repLWn" min="10" max="80" value="50" oninput="document.getElementById('repLW').value=this.value">
      </div>
      <div class="re-row" id="repLDRow" style="display:none">
        <span class="re-label" style="font-size:7px">Notch D%</span>
        <input type="range" class="re-slider" id="repLD" min="10" max="80" value="50" oninput="document.getElementById('repLDn').value=this.value">
        <input type="number" class="re-num" id="repLDn" min="10" max="80" value="50" oninput="document.getElementById('repLD').value=this.value">
      </div>
      <div class="re-row" id="repLOrientRow" style="display:none">
        <span class="re-label" style="font-size:7px">Notch</span>
        <div style="display:flex;gap:4px;flex-wrap:wrap">
          <button class="side-btn sel" id="lorTR" onclick="setLOrient('TR')">↗ Top-Right</button>
          <button class="side-btn"     id="lorTL" onclick="setLOrient('TL')">↖ Top-Left</button>
          <button class="side-btn"     id="lorBR" onclick="setLOrient('BR')">↘ Bot-Right</button>
          <button class="side-btn"     id="lorBL" onclick="setLOrient('BL')">↙ Bot-Left</button>
        </div>
      </div>
      <button class="re-apply" onclick="applyRoomEdit()">✓ Apply Changes</button>
    </div>

    <!-- GRID CONTROLS -->
    <div id="gridControls">
      <div style="font-family:'Space Mono',monospace;font-size:7px;letter-spacing:2px;color:var(--accent3);text-transform:uppercase;margin-bottom:9px">Grid</div>
      <div class="gc-row">
        <span class="gc-label">Size</span>
        <select class="gc-sel" id="gridSz" onchange="drawGridCanvas()">
          <option value="10">10px fine</option>
          <option value="25" selected>25px medium</option>
          <option value="50">50px coarse</option>
          <option value="100">100px large</option>
        </select>
      </div>
      <div class="gc-row">
        <label style="display:flex;align-items:center;gap:6px;cursor:pointer;font-family:'Space Mono',monospace;font-size:8px;color:var(--text)">
          <input type="checkbox" id="gridSnap" checked style="accent-color:var(--accent3)"> Snap
        </label>
      </div>
      <div style="font-family:'Space Mono',monospace;font-size:8px;color:var(--muted);margin-top:6px;padding-top:6px;border-top:1px solid var(--border)">
        Cursor: <span id="cursorPos">—</span>
      </div>
    </div>

    <!-- DRAW GHOST + MEASURE -->
    <div id="drawGhost"></div>
    <div id="measureBadge"></div>

    <!-- TOAST -->
    <div id="toast"></div>

    <!-- HINT -->
    <div id="hint">
      <kbd>drag</kbd> orbit &nbsp;<kbd>scroll</kbd> zoom &nbsp;<kbd>right-drag</kbd> pan &nbsp;<kbd>arrows</kbd> orbit &nbsp;<kbd>shift+arrows</kbd> pan
    </div>

    <!-- DOOR PANEL -->
    <div class="fp" id="doorPanel">
      <button class="fp-close" onclick="toggleDoorPanel()">✕</button>
      <div class="fp-title">🚪 Door Editor — <span id="dpRoomName" style="font-weight:400;color:var(--text);letter-spacing:0"></span></div>
      <div style="font-size:8px;color:var(--muted);font-family:'Space Mono',monospace;margin-bottom:6px">Existing doors:</div>
      <div class="dp-list" id="dpList"><div class="dp-empty">No doors yet</div></div>
      <div style="font-size:8px;color:var(--muted);font-family:'Space Mono',monospace;margin-bottom:7px;border-top:1px solid var(--border);padding-top:8px">Add new door:</div>
      <div class="dp-row"><span class="dp-label">Wall</span>
        <div class="side-row" id="dpSides">
          <button class="side-btn sel" data-side="front" onclick="selDoorSide('front')">Front</button>
          <button class="side-btn" data-side="back" onclick="selDoorSide('back')">Back</button>
          <button class="side-btn" data-side="left" onclick="selDoorSide('left')">Left</button>
          <button class="side-btn" data-side="right" onclick="selDoorSide('right')">Right</button>
        </div>
      </div>
      <div class="dp-row"><span class="dp-label">Offset</span><input type="range" class="dp-slider" id="dpOffset" min="5" max="95" value="50" oninput="document.getElementById('dpOffsetN').value=this.value"><input type="number" class="dp-num" id="dpOffsetN" value="50" oninput="document.getElementById('dpOffset').value=this.value"><span style="font-size:8px;color:var(--muted);font-family:'Space Mono',monospace">%</span></div>
      <div class="dp-row"><span class="dp-label">Width</span><input type="range" class="dp-slider" id="dpWidth" min="10" max="80" value="28" oninput="document.getElementById('dpWidthN').value=this.value"><input type="number" class="dp-num" id="dpWidthN" value="28" oninput="document.getElementById('dpWidth').value=this.value"></div>
      <div class="dp-row"><span class="dp-label">Height %</span><input type="range" class="dp-slider" id="dpHeight" min="20" max="95" value="75" oninput="document.getElementById('dpHeightN').value=this.value"><input type="number" class="dp-num" id="dpHeightN" value="75" oninput="document.getElementById('dpHeight').value=this.value"><span style="font-size:8px;color:var(--muted);font-family:'Space Mono',monospace">%</span></div>
      <button class="dp-add" style="background:linear-gradient(135deg,#fb923c,#c2410c);color:#fff" onclick="addDoor()">＋ Add Door</button>
    </div>

    <!-- WINDOW PANEL -->
    <div class="fp" id="winPanel">
      <button class="fp-close" onclick="toggleWinPanel()">✕</button>
      <div class="fp-title">🪟 Window Editor — <span id="wpRoomName" style="font-weight:400;color:var(--text);letter-spacing:0"></span></div>
      <div style="font-size:8px;color:var(--muted);font-family:'Space Mono',monospace;margin-bottom:6px">Existing windows — click to select:</div>
      <div class="dp-list" id="wpList"><div class="dp-empty">No windows yet</div></div>
      <div id="wpEditBox" style="display:none;border:1px solid rgba(0,229,255,.2);border-radius:6px;padding:9px;margin-bottom:9px;background:rgba(0,229,255,.03)">
        <div style="font-size:8px;color:var(--accent3);font-family:'Space Mono',monospace;letter-spacing:1.5px;text-transform:uppercase;margin-bottom:7px">● Editing window <span id="wpEditIdx"></span></div>
        <div class="dp-row"><span class="dp-label">Wall</span>
          <div class="side-row" id="wpEditSides">
            <button class="side-btn sel-cyan" data-side="front" onclick="editWinSide('front')">Front</button>
            <button class="side-btn" data-side="back" onclick="editWinSide('back')">Back</button>
            <button class="side-btn" data-side="left" onclick="editWinSide('left')">Left</button>
            <button class="side-btn" data-side="right" onclick="editWinSide('right')">Right</button>
          </div>
        </div>
        <div class="dp-row"><span class="dp-label">Position</span><input type="range" class="dp-slider dp-slider-cyan" id="wpEOffset" min="5" max="95" value="50" oninput="document.getElementById('wpEOffsetN').value=this.value;liveWin()"><input type="number" class="dp-num" id="wpEOffsetN" value="50" oninput="document.getElementById('wpEOffset').value=this.value;liveWin()"><span style="font-size:8px;color:var(--muted);font-family:'Space Mono',monospace">%</span></div>
        <div class="dp-row"><span class="dp-label">Width</span><input type="range" class="dp-slider dp-slider-cyan" id="wpEWidth" min="10" max="120" value="40" oninput="document.getElementById('wpEWidthN').value=this.value;liveWin()"><input type="number" class="dp-num" id="wpEWidthN" value="40" oninput="document.getElementById('wpEWidth').value=this.value;liveWin()"></div>
        <div class="dp-row"><span class="dp-label">Height %</span><input type="range" class="dp-slider dp-slider-cyan" id="wpEHeight" min="10" max="80" value="35" oninput="document.getElementById('wpEHeightN').value=this.value;liveWin()"><input type="number" class="dp-num" id="wpEHeightN" value="35" oninput="document.getElementById('wpEHeight').value=this.value;liveWin()"><span style="font-size:8px;color:var(--muted);font-family:'Space Mono',monospace">%</span></div>
        <button onclick="deleteSelWin()" style="width:100%;padding:6px;border-radius:4px;border:1px solid rgba(255,77,109,.3);background:rgba(255,77,109,.07);color:var(--accent2);font-family:'Barlow',sans-serif;font-size:11px;font-weight:700;cursor:pointer">✕ Delete Window</button>
      </div>
      <div style="font-size:8px;color:var(--muted);font-family:'Space Mono',monospace;margin-bottom:7px;border-top:1px solid var(--border);padding-top:8px">Add new window:</div>
      <div class="dp-row"><span class="dp-label">Wall</span>
        <div class="side-row" id="wpSides">
          <button class="side-btn sel-cyan" data-side="front" onclick="selWinSide('front')">Front</button>
          <button class="side-btn" data-side="back" onclick="selWinSide('back')">Back</button>
          <button class="side-btn" data-side="left" onclick="selWinSide('left')">Left</button>
          <button class="side-btn" data-side="right" onclick="selWinSide('right')">Right</button>
        </div>
      </div>
      <div class="dp-row"><span class="dp-label">Position</span><input type="range" class="dp-slider dp-slider-cyan" id="wpOffset" min="5" max="95" value="50" oninput="document.getElementById('wpOffsetN').value=this.value"><input type="number" class="dp-num" id="wpOffsetN" value="50" oninput="document.getElementById('wpOffset').value=this.value"><span style="font-size:8px;color:var(--muted);font-family:'Space Mono',monospace">%</span></div>
      <div class="dp-row"><span class="dp-label">Width</span><input type="range" class="dp-slider dp-slider-cyan" id="wpWidth" min="10" max="120" value="40" oninput="document.getElementById('wpWidthN').value=this.value"><input type="number" class="dp-num" id="wpWidthN" value="40" oninput="document.getElementById('wpWidth').value=this.value"></div>
      <div class="dp-row"><span class="dp-label">Height %</span><input type="range" class="dp-slider dp-slider-cyan" id="wpHeight" min="10" max="80" value="35" oninput="document.getElementById('wpHeightN').value=this.value"><input type="number" class="dp-num" id="wpHeightN" value="35" oninput="document.getElementById('wpHeight').value=this.value"><span style="font-size:8px;color:var(--muted);font-family:'Space Mono',monospace">%</span></div>
      <button class="dp-add" style="background:linear-gradient(135deg,#00e5ff,#0077aa);color:#000" onclick="addWin()">🪟 Add Window</button>
    </div>

    <!-- SLAB TOOLBAR -->
    <div id="slabToolbar">
      <span class="sl-label" id="slabSelName">No slab selected</span>
      <div class="rt-sep"></div>
      <button class="rt-btn" onclick="openAddSlabModal()" style="color:#a78bfa;border-color:rgba(167,139,250,.35)">＋ Add Slab</button>
      <div class="rt-sep"></div>
      <button class="rt-btn" id="btnSlabResize" onclick="openSlabResizePanel()" style="color:var(--muted2)">📐 Resize</button>
      <div class="rt-sep"></div>
      <button class="rt-btn danger" onclick="deleteSelSlab()">✕ Delete</button>
    </div>

    <!-- SLAB RESIZE PANEL -->
    <div class="fp" id="slabPanel">
      <button class="fp-close" onclick="closeSlabResizePanel()">✕</button>
      <div class="fp-title" style="color:#a78bfa">📐 Resize Slab — <span id="slabResName" style="font-weight:400;color:var(--text);letter-spacing:0"></span></div>
      <div style="display:flex;gap:4px;flex-wrap:wrap;margin-bottom:10px">
        <button onclick="slabPreset(BW,BD)" style="padding:4px 9px;border-radius:3px;border:1px solid var(--border);background:var(--s2);color:var(--muted2);font-family:'Space Mono',monospace;font-size:8px;cursor:pointer">Full Floor</button>
        <button onclick="slabPreset(BW/2,BD)" style="padding:4px 9px;border-radius:3px;border:1px solid var(--border);background:var(--s2);color:var(--muted2);font-family:'Space Mono',monospace;font-size:8px;cursor:pointer">Half Width</button>
        <button onclick="slabPreset(BW,BD/2)" style="padding:4px 9px;border-radius:3px;border:1px solid var(--border);background:var(--s2);color:var(--muted2);font-family:'Space Mono',monospace;font-size:8px;cursor:pointer">Half Depth</button>
        <button onclick="slabPreset(400,300)" style="padding:4px 9px;border-radius:3px;border:1px solid var(--border);background:var(--s2);color:var(--muted2);font-family:'Space Mono',monospace;font-size:8px;cursor:pointer">400×300</button>
      </div>
      <div class="dp-row"><span class="dp-label">Width</span><input type="range" class="dp-slider dp-slider-violet" id="slabRW" min="20" max="800" value="800" oninput="document.getElementById('slabRWn').value=this.value"><input type="number" class="dp-num" id="slabRWn" value="800" oninput="document.getElementById('slabRW').value=this.value"></div>
      <div class="dp-row"><span class="dp-label">Depth</span><input type="range" class="dp-slider dp-slider-violet" id="slabRD" min="20" max="600" value="600" oninput="document.getElementById('slabRDn').value=this.value"><input type="number" class="dp-num" id="slabRDn" value="600" oninput="document.getElementById('slabRD').value=this.value"></div>
      <div class="dp-row"><span class="dp-label">Thick</span><input type="range" class="dp-slider dp-slider-violet" id="slabRT" min="2" max="30" value="7" oninput="document.getElementById('slabRTn').value=this.value"><input type="number" class="dp-num" id="slabRTn" value="7" oninput="document.getElementById('slabRT').value=this.value"></div>
      <button onclick="applySlabResize()" style="width:100%;padding:8px;border-radius:4px;border:none;background:linear-gradient(135deg,#a78bfa,#6d28d9);color:#fff;font-family:'Barlow',sans-serif;font-size:11px;font-weight:800;cursor:pointer;margin-top:2px;text-transform:uppercase;letter-spacing:.5px">✓ Apply</button>
    </div>

    <!-- ADD SLAB MODAL (inside modal overlay) -->

    <!-- FLOOR POSITION PANEL -->
    <div class="fp" id="floorPosPanel">
      <button class="fp-close" onclick="closeFloorPos()">✕</button>
      <div class="fp-title">📐 Floor Position — <span id="fpFloorName" style="font-weight:400;color:var(--text);letter-spacing:0"></span></div>
      <div class="dp-row"><span class="dp-label">Name</span><input type="text" id="fpName" class="dp-num" style="width:140px;text-align:left" oninput="liveFloorName()"></div>
      <div class="dp-row"><span class="dp-label">Short</span><input type="text" id="fpShort" class="dp-num" style="width:60px;text-align:left" maxlength="3" oninput="liveFloorShort()"></div>
      <div style="height:1px;background:var(--border);margin:8px 0"></div>
      <div class="dp-row">
        <span class="dp-label">X Offset</span>
        <input type="range" class="dp-slider" id="fpX" min="-500" max="500" value="0" oninput="document.getElementById('fpXn').value=this.value;liveFloorPos()">
        <input type="number" class="dp-num" id="fpXn" value="0" oninput="document.getElementById('fpX').value=this.value;liveFloorPos()">
      </div>
      <div class="dp-row">
        <span class="dp-label">Z Offset</span>
        <input type="range" class="dp-slider" id="fpZ" min="-500" max="500" value="0" oninput="document.getElementById('fpZn').value=this.value;liveFloorPos()">
        <input type="number" class="dp-num" id="fpZn" value="0" oninput="document.getElementById('fpZ').value=this.value;liveFloorPos()">
      </div>
      <div class="dp-row">
        <span class="dp-label">Y Offset</span>
        <input type="range" class="dp-slider" id="fpY" min="-200" max="200" value="0" oninput="document.getElementById('fpYn').value=this.value;liveFloorPos()">
        <input type="number" class="dp-num" id="fpYn" value="0" oninput="document.getElementById('fpY').value=this.value;liveFloorPos()">
      </div>
      <button onclick="resetFloorPos()" style="width:100%;margin-top:6px;padding:6px;border-radius:4px;border:1px solid var(--border2);background:var(--s2);color:var(--muted2);font-family:'Barlow',sans-serif;font-size:10px;font-weight:700;cursor:pointer;text-transform:uppercase;letter-spacing:.5px">↺ Reset Position</button>
    </div>

    <!-- STAIRS PANEL -->
    <div class="fp" id="stairsPanel" style="top:14px;left:50%;transform:translateX(-50%);min-width:370px;border-color:rgba(234,179,8,.3)">
      <button class="fp-close" onclick="document.getElementById('stairsPanel').classList.remove('show')">✕</button>
      <div class="fp-title" style="color:#eab308">🪜 Staircase Editor</div>
      <div style="font-size:8px;color:var(--muted);font-family:'Space Mono',monospace;margin-bottom:8px">Existing staircases:</div>
      <div class="dp-list" id="stairList"><div class="dp-empty">No staircases yet</div></div>
      <div style="font-size:8px;color:var(--muted);font-family:'Space Mono',monospace;margin-bottom:7px;border-top:1px solid var(--border);padding-top:8px">Add new staircase:</div>
      <div class="dp-row"><span class="dp-label">Name</span><input type="text" id="stairName" value="Staircase" class="dp-num" style="width:120px;text-align:left"></div>
      <div class="dp-row"><span class="dp-label">Floor From</span>
        <select id="stairFrom" class="dp-num" style="width:90px;text-align:left"></select>
        <span style="font-size:8px;color:var(--muted);font-family:'Space Mono',monospace;margin:0 4px">→</span>
        <select id="stairTo" class="dp-num" style="width:90px;text-align:left"></select>
      </div>
      <div class="dp-row"><span class="dp-label">Width</span><input type="range" class="dp-slider" id="stairW" min="20" max="200" value="60" style="--thumb:#eab308" oninput="document.getElementById('stairWn').value=this.value"><input type="number" class="dp-num" id="stairWn" value="60" oninput="document.getElementById('stairW').value=this.value"></div>
      <div class="dp-row"><span class="dp-label">Depth</span><input type="range" class="dp-slider" id="stairD" min="30" max="300" value="120" oninput="document.getElementById('stairDn').value=this.value"><input type="number" class="dp-num" id="stairDn" value="120" oninput="document.getElementById('stairD').value=this.value"></div>
      <div class="dp-row"><span class="dp-label">Height</span><input type="range" class="dp-slider" id="stairH" min="10" max="300" value="90" oninput="document.getElementById('stairHn').value=this.value"><input type="number" class="dp-num" id="stairHn" value="90" oninput="document.getElementById('stairH').value=this.value"></div>
      <div class="dp-row"><span class="dp-label">Length</span><input type="range" class="dp-slider" id="stairL" min="30" max="500" value="120" oninput="document.getElementById('stairLn').value=this.value"><input type="number" class="dp-num" id="stairLn" value="120" oninput="document.getElementById('stairL').value=this.value"></div>
      <div class="dp-row"><span class="dp-label">X Pos</span><input type="range" class="dp-slider" id="stairX" min="0" max="750" value="100" oninput="document.getElementById('stairXn').value=this.value"><input type="number" class="dp-num" id="stairXn" value="100" oninput="document.getElementById('stairX').value=this.value"></div>
      <div class="dp-row"><span class="dp-label">Z Pos</span><input type="range" class="dp-slider" id="stairZ" min="0" max="550" value="100" oninput="document.getElementById('stairZn').value=this.value"><input type="number" class="dp-num" id="stairZn" value="100" oninput="document.getElementById('stairZ').value=this.value"></div>
      <div class="dp-row"><span class="dp-label">Style</span>
        <div style="display:flex;gap:4px">
          <button class="side-btn sel" id="sStyleStraight" onclick="selStairStyle('straight')">Straight</button>
          <button class="side-btn" id="sStyleL" onclick="selStairStyle('L')">L-Shape</button>
          <button class="side-btn" id="sStyleU" onclick="selStairStyle('U')">U-Shape</button>
          <button class="side-btn" id="sStyleCurve" onclick="selStairStyle('curve')">Curved</button>
          <button class="side-btn" id="sStyleSpiral" onclick="selStairStyle('spiral')">Spiral</button>
        </div>
      </div>
      <button class="dp-add" style="background:linear-gradient(135deg,#eab308,#a16207);color:#000" onclick="addStaircase()">🪜 Add Staircase</button>
    </div>

    <!-- STATS PANEL -->
    <div id="statsPanel" style="position:absolute;top:54px;right:12px;background:rgba(8,10,16,.97);backdrop-filter:blur(18px);border:1px solid var(--border2);border-radius:8px;padding:14px 16px;z-index:100;display:none;min-width:220px;box-shadow:0 20px 60px rgba(0,0,0,.6)">
      <button class="fp-close" onclick="toggleStatsPanel()">✕</button>
      <div class="fp-title" style="color:#a78bfa">
        <svg width="12" height="12" viewBox="0 0 13 13" fill="none"><rect x="1" y="1" width="11" height="11" rx="1.2" stroke="currentColor" stroke-width="1.3"/><path d="M3.5 9V6.5M6.5 9V4M9.5 9V7" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/></svg>
        Floor Stats
      </div>
      <div id="statsContent" style="font-family:'Space Mono',monospace;font-size:9px;color:var(--muted2);line-height:1.8"></div>
    </div>

    <!-- OVERLAP REPORT PANEL -->
    <div id="overlapPanel">
      <button class="fp-close" onclick="toggleOverlapPanel()">✕</button>
      <div class="fp-title">
        <svg width="12" height="12" viewBox="0 0 13 13" fill="none"><rect x="1.5" y="1.5" width="6" height="6" rx=".8" stroke="currentColor" stroke-width="1.3"/><rect x="5.5" y="5.5" width="6" height="6" rx=".8" stroke="currentColor" stroke-width="1.3"/></svg>
        Overlap Check
      </div>
      <div id="overlapContent"></div>
    </div>

    <!-- DB BACKDROP -->
    <div id="dbBackdrop" onclick="closeSaveLoad()"></div>

    <!-- SAVE/LOAD PANEL -->
    <div class="fp" id="dbPanel" style="position:absolute">
      <button class="fp-close" onclick="closeSaveLoad()">✕</button>
      <div class="fp-title" style="color:#34d399">
        <svg width="13" height="13" viewBox="0 0 13 13" fill="none"><rect x="1" y="3" width="11" height="8.5" rx="1" stroke="#34d399" stroke-width="1.2"/><path d="M1 5.5h11" stroke="#34d399" stroke-width="1.1"/></svg>
        Layout Database
      </div>
      <div style="font-family:'Space Mono',monospace;font-size:7px;color:var(--muted);margin-bottom:12px">Save and restore complete building layouts</div>
      <div style="font-family:'Space Mono',monospace;font-size:7px;letter-spacing:2px;color:var(--muted);text-transform:uppercase;margin-bottom:6px">Save Current</div>
      <div class="db-name-row">
        <input class="db-name-input" id="dbName" placeholder="Layout name...">
        <button class="db-save-btn" onclick="dbSave()">Save</button>
      </div>
      <div style="font-family:'Space Mono',monospace;font-size:7px;letter-spacing:2px;color:var(--muted);text-transform:uppercase;margin-bottom:6px">Saved Layouts</div>
      <div class="db-list" id="dbList"></div>
      <div class="db-io">
        <button class="db-io-btn" onclick="dbExport()">⬇ Export JSON</button>
        <button class="db-io-btn" onclick="document.getElementById('dbImport').click()">⬆ Import JSON</button>
        <input type="file" id="dbImport" accept=".json" style="display:none" onchange="dbImportFile(event)">
      </div>
    </div>

    <!-- ADD ROOM MODAL -->
    <div id="modalOverlay">
      <div class="modal-box" id="modalBox"></div>
    </div>
  </div>
</div>

<!-- STATUS BAR -->
<div id="statusbar">
  <div class="sb-it"><div class="sb-dot"></div>&nbsp;Ready</div>
  <div class="sb-it" style="margin-left:6px"><kbd>drag</kbd> orbit &nbsp;<kbd>scroll</kbd> zoom &nbsp;<kbd>right-drag</kbd> pan &nbsp;<kbd>Q/E</kbd> rotate room &nbsp;<kbd>Ctrl+Z</kbd> undo &nbsp;<kbd>Ctrl+D</kbd> dupe &nbsp;<kbd>Del</kbd> delete</div>
  <span id="statusMsg"></span>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
<script>
// ═══════════════════════════════
// STATE
// ═══════════════════════════════
let BW = 800, BD = 600, FH = 90;
const SLAB = 6, WT = 8;
let FLOORS = [];          // [{id,label,short,rooms:[]}]
let activeFloor = 0;
let currentView = '3d';

// 3D scene objects
const scene = new THREE.Scene();
const renderer = new THREE.WebGLRenderer({ canvas: document.getElementById('c3d'), antialias: true });
renderer.setPixelRatio(Math.min(devicePixelRatio, 2));
renderer.shadowMap.enabled = true;
renderer.setClearColor(0x07080c);
renderer.toneMapping = THREE.ACESFilmicToneMapping;
renderer.toneMappingExposure = 1.1;
const camera = new THREE.PerspectiveCamera(42, 1, 1, 6000);

let sph = { theta: -0.55, phi: 0.72, r: 1600 };
let tgt = new THREE.Vector3(BW/2, 0, BD/2);
let dragging = false, rDragging = false, lm = { x:0, y:0 };

// Room meshes: [{room, fi, ri, mesh, edgeMesh, roofMesh, labelSprite, rotY}]
let roomMeshes = [];
// Floor 3D groups: [{grp, wallGrp, fi, Y}]
let floorGrps = [];

// Selection & interaction
let selEntry = null, hovEntry = null;
let moveTool = false, selectTool = true;
let roomDragging = false, rdx = 0, rdz = 0;
let drawMode = false, isDrawing = false, drawStart = null, drawEnd = null;

// Grid
let gridPan = { x: 0, y: 0 }, gridZoom = 1.0;
let gridDragging = false, gridDragStart = {x:0,y:0}, gridPanStart = {x:0,y:0};

// Selected room type for draw
let selType = { name:'Room', color:'#4a90c4', icon:'🏠' };

const TYPES = [
  { name:'Living Room',   color:'#c96a60', icon:'🛋️' },
  { name:'Bedroom',       color:'#4a90c4', icon:'🛏️' },
  { name:'Kitchen',       color:'#d4a017', icon:'🍳' },
  { name:'Bathroom',      color:'#4aad7a', icon:'🚿' },
  { name:'Office',        color:'#7c6fa0', icon:'💼' },
  { name:'Hallway',       color:'#5b8dd9', icon:'🚶' },
  { name:'Staircase',     color:'#8899bb', icon:'🪜' },
  { name:'Storage',       color:'#3aafa9', icon:'📦' },
  { name:'Dining Room',   color:'#e07b54', icon:'🍽️' },
  { name:'Garage',        color:'#4a5470', icon:'🚗' },
  { name:'Classroom',     color:'#4a90c4', icon:'📚' },
  { name:'Office Open',   color:'#d4a017', icon:'🏢' },
  { name:'L-Shape Room',  color:'#34a0a4', icon:'📐', shape:'L' },
  { name:'Custom',        color:'#a78bfa', icon:'✏️' },
];

const COLORS = ['#c96a60','#4a90c4','#4aad7a','#d4a017','#7c6fa0','#e07b54','#5b8dd9','#3aafa9','#8899bb','#a78bfa','#fb923c','#34d399'];

const raycaster = new THREE.Raycaster();
const mouse = new THREE.Vector2();
const dragPlane = new THREE.Plane(new THREE.Vector3(0,1,0), 0);
const dragIntersect = new THREE.Vector3();
let dragOffset = new THREE.Vector3();
let toastTimer = null;

// ═══════════════════════════════
// SCENE SETUP
// ═══════════════════════════════
scene.fog = new THREE.FogExp2(0x07080c, 0.0006);
const gnd = new THREE.Mesh(new THREE.PlaneGeometry(6000,6000), new THREE.MeshStandardMaterial({color:0x050608,roughness:1}));
gnd.rotation.x = -Math.PI/2; gnd.position.y = -2; gnd.receiveShadow = true; scene.add(gnd);
const gridH = new THREE.GridHelper(4000,80,0x0d1018,0x0d1018); gridH.position.y=-1; scene.add(gridH);
const amb = new THREE.AmbientLight(0x7080aa, 0.65); scene.add(amb);
const sun = new THREE.DirectionalLight(0xfff0d0, 1.5);
sun.position.set(600,900,400); sun.castShadow=true; sun.shadow.mapSize.set(2048,2048);
sun.shadow.camera.left=-1200; sun.shadow.camera.right=1200; sun.shadow.camera.top=1200; sun.shadow.camera.bottom=-1200;
scene.add(sun);
const fill = new THREE.DirectionalLight(0x3050ff,0.3); fill.position.set(-400,400,600); scene.add(fill);

function camUp(){
  camera.position.set(
    tgt.x + sph.r * Math.sin(sph.phi) * Math.sin(sph.theta),
    tgt.y + sph.r * Math.cos(sph.phi),
    tgt.z + sph.r * Math.sin(sph.phi) * Math.cos(sph.theta)
  );
  camera.lookAt(tgt);
}

// ═══════════════════════════════
// FLOOR MANAGEMENT
// ═══════════════════════════════
function addFloor() {
  const fi = FLOORS.length;
  const Y = fi * FH;
  const labels = ['Ground Floor','2nd Floor','3rd Floor','4th Floor','5th Floor','6th Floor','7th Floor','8th Floor'];
  const shorts = ['GF','2F','3F','4F','5F','6F','7F','8F'];
  const fl = { id: fi+1, label: labels[fi] || `Floor ${fi+1}`, short: shorts[fi] || `F${fi+1}`, rooms: [], ox:0, oy:0, oz:0 };
  FLOORS.push(fl);

  // Build 3D group for floor walls
  const grp = new THREE.Group();
  const wMat = new THREE.MeshStandardMaterial({color:0x1c2438,roughness:0.55,metalness:0.2});
  const wallH = FH - SLAB, wallY = Y + SLAB + wallH/2;
  // Slab
  const slabM = new THREE.Mesh(new THREE.BoxGeometry(BW+WT*2, SLAB, BD+WT*2), new THREE.MeshStandardMaterial({color:0x141824,roughness:0.7}));
  slabM.position.set(BW/2, Y+SLAB/2, BD/2); slabM.castShadow=true; slabM.receiveShadow=true; grp.add(slabM);
  // Walls (transparent by default — rooms define the space)
  [[BW+WT*2,wallH,WT, BW/2,wallY,-WT/2],[BW+WT*2,wallH,WT, BW/2,wallY,BD+WT/2],
   [WT,wallH,BD, -WT/2,wallY,BD/2],[WT,wallH,BD, BW+WT/2,wallY,BD/2]].forEach(([w,h,d,x,y,z])=>{
    const m = new THREE.Mesh(new THREE.BoxGeometry(w,h,d), wMat.clone());
    m.position.set(x,y,z); m.castShadow=true; grp.add(m);
  });
  // Badge
  const bSp = new THREE.Sprite(new THREE.SpriteMaterial({map:makeTex(fl.short,'rgba(232,255,60,1)',36),depthTest:false}));
  bSp.scale.set(50,12,1); bSp.position.set(-WT-22, Y+SLAB+wallH/2, BD/2); grp.add(bSp);

  scene.add(grp);
  floorGrps.push({grp, fi, Y, floor:fl});
  refreshFloorUI();
  activateFloor(fi);
  updateEmptyState();
  toast(`＋ ${fl.label} added`);
}

function duplicateFloor(srcFi) {
  const src = FLOORS[srcFi]; if (!src) return;
  const newFi = FLOORS.length, Y = newFi * FH;
  const labels = ['Ground Floor','2nd Floor','3rd Floor','4th Floor','5th Floor','6th Floor','7th Floor','8th Floor'];
  const shorts  = ['GF','2F','3F','4F','5F','6F','7F','8F'];
  const fl = { id:newFi+1, label:labels[newFi]||`Floor ${newFi+1}`, short:shorts[newFi]||`F${newFi+1}`,
               rooms:[], ox:src.ox||0, oy:src.oy||0, oz:src.oz||0 };
  FLOORS.push(fl);

  // Build 3D shell
  const grp = new THREE.Group();
  const wMat = new THREE.MeshStandardMaterial({color:0x1c2438,roughness:0.55,metalness:0.2});
  const wallH = FH-SLAB, wallY = Y+SLAB+wallH/2;
  const slabM = new THREE.Mesh(new THREE.BoxGeometry(BW+WT*2,SLAB,BD+WT*2),new THREE.MeshStandardMaterial({color:0x141824,roughness:0.7}));
  slabM.position.set(BW/2,Y+SLAB/2,BD/2); slabM.castShadow=true; slabM.receiveShadow=true; grp.add(slabM);
  [[BW+WT*2,wallH,WT,BW/2,wallY,-WT/2],[BW+WT*2,wallH,WT,BW/2,wallY,BD+WT/2],
   [WT,wallH,BD,-WT/2,wallY,BD/2],[WT,wallH,BD,BW+WT/2,wallY,BD/2]].forEach(([w,h,d,x,y,z])=>{
    const m=new THREE.Mesh(new THREE.BoxGeometry(w,h,d),wMat.clone()); m.position.set(x,y,z); m.castShadow=true; grp.add(m);
  });
  const bSp=new THREE.Sprite(new THREE.SpriteMaterial({map:makeTex(fl.short,'rgba(232,255,60,1)',36),depthTest:false}));
  bSp.scale.set(50,12,1); bSp.position.set(-WT-22,Y+SLAB+wallH/2,BD/2); grp.add(bSp);
  scene.add(grp); floorGrps.push({grp, fi:newFi, Y, floor:fl});

  // Duplicate rooms
  src.rooms.forEach((r, ri) => {
    const nr = { name:r.name, color:r.color, h:r.h||60, w:r.w, d:r.d, x:r.x, z:r.z,
                 doors:(r.doors||[]).map(d=>({...d})), windows:(r.windows||[]).map(w=>({...w})) };
    fl.rooms.push(nr);
    const srcE = roomMeshes.find(e=>e.fi===srcFi&&e.ri===ri);
    const e = buildRoomMeshes(nr, newFi, ri);
    if (srcE) { e.rotY=srcE.rotY||0; [e.mesh,e.edgeMesh].forEach(o=>{if(o)o.rotation.y=e.rotY;}); if(e.roofMesh){if(e.roofMesh._isLRoof)e.roofMesh.children.forEach(c=>{c.rotation.y=e.rotY;});else e.roofMesh.rotation.y=e.rotY;} }
    rebuildDoorMeshes({fi:newFi, ri, room:nr});
    rebuildWinMeshes ({fi:newFi, ri, room:nr});
  });

  // Duplicate staircases that start on the source floor
  staircases.filter(sc => sc.fi === srcFi).forEach(sc => {
    const srcGrp = stairMeshGrps.get(sc.id);
    const newId  = ++_stairId;
    const newSc  = {
      ...sc,
      id:   newId,
      fi:   newFi,
      fiTo: newFi + (sc.fiTo - sc.fi),   // preserve relative floor span
      x:    srcGrp ? srcGrp.position.x : sc.x,
      z:    srcGrp ? srcGrp.position.z : sc.z,
      rotY: srcGrp ? srcGrp.rotation.y : (sc.rotY||0)
    };
    staircases.push(newSc);
    const g = buildStairMesh(newSc);
    if (g) stairMeshGrps.set(newId, g);
  });

  refreshFloorUI(); refreshStairList(); activateFloor(newFi); updateEmptyState(); autoSave();
  const stairCount = staircases.filter(sc=>sc.fi===newFi).length;
  toast(`⧉ ${src.label} duplicated → ${fl.label} (${src.rooms.length} rooms, ${stairCount} stairs)`);
}

function disposeGroup(grp) {
  grp.traverse(obj => {
    if (obj.geometry) obj.geometry.dispose();
    if (obj.material) {
      if (Array.isArray(obj.material)) obj.material.forEach(m => m.dispose());
      else obj.material.dispose();
    }
  });
  scene.remove(grp);
}

function removeTopFloor() {
  if (FLOORS.length === 0) { toast('⚠ No floors to remove'); return; }
  const fi = FLOORS.length - 1;
  // Remove doors/windows on this floor
  for (const [k,g] of doorMeshMap) { if(k.startsWith(fi+'-')){ disposeGroup(g); doorMeshMap.delete(k); } }
  for (const [k,g] of winMeshMap)  { if(k.startsWith(fi+'-')){ disposeGroup(g); winMeshMap.delete(k); } }
  // Remove room meshes on this floor
  roomMeshes.filter(e=>e.fi===fi).forEach(e=>{
    [e.mesh,e.edgeMesh].forEach(o=>{ if(o){ if(o.geometry)o.geometry.dispose(); if(o.material)o.material.dispose(); scene.remove(o); } });
    if(e.roofMesh){ if(e.roofMesh._isLRoof){ e.roofMesh.children.forEach(c=>{if(c.geometry)c.geometry.dispose();if(c.material)c.material.dispose();}); scene.remove(e.roofMesh); } else { if(e.roofMesh.geometry)e.roofMesh.geometry.dispose(); if(e.roofMesh.material)e.roofMesh.material.dispose(); scene.remove(e.roofMesh); } }
    if(e.labelSprite){ e.labelSprite.material.map?.dispose(); e.labelSprite.material.dispose(); scene.remove(e.labelSprite); }
    roomMeshes.splice(roomMeshes.indexOf(e),1);
  });
  // Remove floor group
  const fg = floorGrps.find(g=>g.fi===fi);
  if(fg){ disposeGroup(fg.grp); floorGrps.splice(floorGrps.indexOf(fg),1); }
  FLOORS.pop();
  if(activeFloor >= FLOORS.length) activeFloor = Math.max(0, FLOORS.length-1);
  if(FLOORS.length > 0) activateFloor(activeFloor);
  refreshFloorUI();
  rebuildRoomList();
  updateEmptyState();
  autoSave();
  toast('🗑 Floor removed');
}

function activateFloor(fi) {
  if (fi < 0 || fi >= FLOORS.length) return;
  activeFloor = fi;
  // Dim other floors, show active
  floorGrps.forEach((fg,i)=>{
    const isAct = i===fi;
    fg.grp.children.forEach(obj=>{
      if(obj.isMesh && obj.material){
        obj.material.transparent = true;
        obj.material.opacity = isAct ? 1.0 : 0.12;
        obj.material.needsUpdate = true;
      }
    });
  });
  // Room meshes
  roomMeshes.forEach(e=>{
    const isAct = e.fi===fi;
    [e.mesh,e.edgeMesh].forEach(o=>{
      if(o&&o.material){ o.material.transparent=true; o.material.opacity=isAct?0.88:0.08; o.material.needsUpdate=true; }
    });
    setRoofOpacity(e.roofMesh, isAct?0.07:0.02);
    if(e.labelSprite) e.labelSprite.material.opacity = isAct ? 1 : 0.08;
  });
  refreshFloorUI();
  rebuildRoomList();
  if(currentView==='grid') drawGridCanvas();
  if(currentView==='floor'){
    floorGrps.forEach((fg,i)=>{fg.grp.visible=(i===fi);});
    roomMeshes.forEach(e=>{
      const s=e.fi===fi;
      [e.mesh,e.edgeMesh,e.labelSprite].forEach(o=>{if(o)o.visible=s;});
      setRoofVisible(e.roofMesh, s);
    });
    staircases.forEach(sc=>{
      const grp=stairMeshGrps.get(sc.id);
      if(grp) grp.visible=(sc.fi===fi);
    });
  }
  setStatus(`${FLOORS[fi]?.label}`);
}

function refreshFloorUI() {
  const el = document.getElementById('floorList');
  el.innerHTML = FLOORS.map((fl,i)=>`
    <div style="display:flex;align-items:center;gap:3px;padding:2px 6px">
      <button class="fl-row${i===activeFloor?' active':''}" style="flex:1;border-radius:4px" onclick="activateFloor(${i})">
        <span class="fl-badge">${fl.short}</span>
        <div>
          <div class="fl-name">${fl.label}</div>
          <div class="fl-meta">${fl.rooms.length} room${fl.rooms.length!==1?'s':''} · pos(${fl.ox||0},${fl.oy||0},${fl.oz||0})</div>
        </div>
      </button>
      <button title="Duplicate floor" onclick="duplicateFloor(${i})" style="flex-shrink:0;width:26px;height:26px;border-radius:4px;border:1px solid var(--border);background:transparent;color:var(--muted2);cursor:pointer;font-size:13px;display:flex;align-items:center;justify-content:center;transition:all .12s" onmouseover="this.style.background='var(--s2)';this.style.color='var(--accent3)'" onmouseout="this.style.background='transparent';this.style.color='var(--muted2)'">⧉</button>
      <button title="Edit position" onclick="openFloorPos(${i})" style="flex-shrink:0;width:26px;height:26px;border-radius:4px;border:1px solid var(--border);background:transparent;color:var(--muted2);cursor:pointer;font-size:12px;display:flex;align-items:center;justify-content:center;transition:all .12s" onmouseover="this.style.background='var(--s2)';this.style.color='var(--accent)'" onmouseout="this.style.background='transparent';this.style.color='var(--muted2)'">⊹</button>
    </div>`).join('');
}

// ═══════════════════════════════
// ROOM BUILDING
// ═══════════════════════════════
function makeTex(text, col='rgba(255,255,255,.92)', fs=24) {
  const c = document.createElement('canvas'); c.width=512; c.height=128;
  const ctx = c.getContext('2d');
  ctx.font=`bold ${fs}px Barlow,sans-serif`;
  ctx.fillStyle=col; ctx.textAlign='center'; ctx.textBaseline='middle';
  const words=text.split(' ');
  if(text.length<=14||words.length<=1){ ctx.fillText(text,256,64); }
  else{ const h=Math.ceil(words.length/2); ctx.font=`bold ${fs-4}px Barlow,sans-serif`; ctx.fillText(words.slice(0,h).join(' '),256,38); ctx.fillText(words.slice(h).join(' '),256,80); }
  return new THREE.CanvasTexture(c);
}

// Build an L-shaped ExtrudeGeometry. The L occupies a w×d bounding box with
// a rectangular notch cut from the top-right corner. lw/ld are the notch
// fractions (0–1), defaulting to 0.5.
// Returns the 6 XZ corner points of an L-shape for a given notch orientation
function getLShapePts(w, d, lw, ld, orient) {
  switch(orient) {
    case 'TL': return [[0,0],[w,0],[w,d],[lw,d],[lw,d-ld],[0,d-ld]];
    case 'BR': return [[0,0],[w-lw,0],[w-lw,ld],[w,ld],[w,d],[0,d]];
    case 'BL': return [[0,ld],[lw,ld],[lw,0],[w,0],[w,d],[0,d]];
    default:   return [[0,0],[w,0],[w,d-ld],[w-lw,d-ld],[w-lw,d],[0,d]]; // TR
  }
}

function buildLShapeGeo(w, h, d, lwFrac, ldFrac, orient) {
  const lw = w * (lwFrac || 0.5);
  const ld = d * (ldFrac || 0.5);

  // Always split the L into exactly 2 axis-aligned rectangles.
  // rect1 = the full-width/depth strip, rect2 = the remaining arm.
  // This avoids any polygon-split ambiguity regardless of orientation.
  let rects; // each: [x0, z0, x1, z1]
  switch(orient) {
    case 'TL': // notch top-left: arm goes bottom-full-width + right-top
      rects = [[0,0,w,d-ld], [lw,d-ld,w,d]]; break;
    case 'BR': // notch bottom-right: arm goes full-width-top + left-bottom
      rects = [[0,ld,w,d], [0,0,w-lw,ld]]; break;
    case 'BL': // notch bottom-left: arm goes full-width-top + right-bottom
      rects = [[0,ld,w,d], [lw,0,w,ld]]; break;
    default:   // TR: notch top-right
      rects = [[0,0,w,d-ld], [0,d-ld,w-lw,d]]; break;
  }

  const positions = [], normals = [], indices = [];

  function addRect(x0,z0,x1,z1, yBot, yTop) {
    // top face
    let base = positions.length/3;
    positions.push(x0,yTop,z0, x1,yTop,z0, x1,yTop,z1, x0,yTop,z1);
    normals.push(0,1,0, 0,1,0, 0,1,0, 0,1,0);
    indices.push(base,base+1,base+2, base,base+2,base+3);
    // bottom face
    base = positions.length/3;
    positions.push(x0,yBot,z0, x1,yBot,z0, x1,yBot,z1, x0,yBot,z1);
    normals.push(0,-1,0, 0,-1,0, 0,-1,0, 0,-1,0);
    indices.push(base,base+2,base+1, base,base+3,base+2);
  }

  function addWall(ax,az, bx,bz, yBot, yTop) {
    const base = positions.length/3;
    positions.push(ax,yBot,az, bx,yBot,bz, bx,yTop,bz, ax,yTop,az);
    const nx=(bz-az), nz=-(bx-ax), len=Math.sqrt(nx*nx+nz*nz)||1;
    normals.push(nx/len,0,nz/len, nx/len,0,nz/len, nx/len,0,nz/len, nx/len,0,nz/len);
    indices.push(base,base+2,base+1, base,base+3,base+2);
  }

  const yBot = -h/2, yTop = h/2;

  // Top & bottom faces — two clean rectangles, no overlap, no gap
  rects.forEach(([x0,z0,x1,z1]) => addRect(x0,z0,x1,z1, yBot, yTop));

  // Side walls — walk the 6 outer edges of the L
  const pts = getLShapePts(w, d, lw, ld, orient || 'TR');
  for(let i=0;i<pts.length;i++)
    addWall(pts[i][0],pts[i][1], pts[(i+1)%pts.length][0],pts[(i+1)%pts.length][1], yBot, yTop);

  const geo = new THREE.BufferGeometry();
  geo.setAttribute('position', new THREE.Float32BufferAttribute(positions, 3));
  geo.setAttribute('normal',   new THREE.Float32BufferAttribute(normals, 3));
  geo.setIndex(indices);
  geo.computeVertexNormals();
  return geo;
}

// Helper: apply opacity or visibility to a roofMesh (may be a THREE.Group for L-shapes)
function setRoofOpacity(roofMesh, opacity) {
  if (!roofMesh) return;
  if (roofMesh._isLRoof) {
    roofMesh.children.forEach(c => { if(c.material){c.material.transparent=true;c.material.opacity=opacity;c.material.needsUpdate=true;} });
  } else if (roofMesh.material) {
    roofMesh.material.transparent=true; roofMesh.material.opacity=opacity; roofMesh.material.needsUpdate=true;
  }
}
function setRoofVisible(roofMesh, vis) {
  if (!roofMesh) return;
  if (roofMesh._isLRoof) roofMesh.children.forEach(c=>{ c.visible=vis; });
  else roofMesh.visible = vis;
}

function buildRoomMeshes(room, fi, ri) {
  const Y = fi * FH;
  const rY = Y + SLAB;
  const rH = room.h || 60;
  const rc = new THREE.Color(room.color);
  const isL = room.shape === 'L';

  let geo, cx, cz;
  if (isL) {
    geo = buildLShapeGeo(room.w, rH, room.d, room.lw || 0.5, room.ld || 0.5, room.lorient || 'TR');
    cx = room.x; cz = room.z;
  } else {
    geo = new THREE.BoxGeometry(room.w, rH, room.d);
    cx = room.x + room.w/2; cz = room.z + room.d/2;
  }

  const mat = new THREE.MeshStandardMaterial({
    color:rc, roughness:0.5, metalness:0.1, transparent:true, opacity:0.88,
    side: isL ? THREE.DoubleSide : THREE.FrontSide
  });
  const mesh = new THREE.Mesh(geo, mat);
  mesh.position.set(cx, rY+rH/2, cz); mesh.castShadow=true; mesh.receiveShadow=true; mesh.userData={fi,ri}; scene.add(mesh);

  let eM;
  if (isL) {
    const lw = room.w * (room.lw || 0.5);
    const ld = room.d * (room.ld || 0.5);
    const pts = getLShapePts(room.w, room.d, lw, ld, room.lorient || 'TR');
    const yT = rH/2, yB = -rH/2;
    const segs = [];
    const n = pts.length;
    for (let i=0;i<n;i++){
      const [ax,az]=pts[i], [bx,bz]=pts[(i+1)%n];
      segs.push(ax,yT,az, bx,yT,bz);
      segs.push(ax,yB,az, bx,yB,bz);
      segs.push(ax,yT,az, ax,yB,az);
    }
    const eMGeo = new THREE.BufferGeometry();
    eMGeo.setAttribute('position', new THREE.Float32BufferAttribute(segs, 3));
    eM = new THREE.LineSegments(eMGeo, new THREE.LineBasicMaterial({color:rc, transparent:true, opacity:0.5}));
    eM.position.set(cx, rY+rH/2, cz);
    scene.add(eM);
  } else {
    eM = new THREE.LineSegments(new THREE.EdgesGeometry(geo), new THREE.LineBasicMaterial({color:rc,transparent:true,opacity:0.4}));
    eM.position.set(cx, rY+rH/2, cz); scene.add(eM);
  }

  // Roof cap — skip for L-shape (two-box approximation causes ghost artifacts)
  let rfM;
  if (!isL) {
    rfM = new THREE.Mesh(new THREE.BoxGeometry(room.w-2,2,room.d-2), new THREE.MeshStandardMaterial({color:rc,transparent:true,opacity:0.07}));
    rfM.position.set(cx, rY+rH+1, cz); scene.add(rfM);
  } else {
    rfM = null;
  }

  const lSp = new THREE.Sprite(new THREE.SpriteMaterial({map:makeTex(room.name),depthTest:false,transparent:true}));
  const ls = Math.max(room.w,60)*0.6;
  const lx = isL ? room.x + room.w * 0.35 : cx;
  const lz = isL ? room.z + room.d * 0.35 : cz;
  lSp.scale.set(ls,ls*.22,1); lSp.position.set(lx, rY+rH+10, lz); scene.add(lSp);
  const entry = {room, fi, ri, mesh, edgeMesh:eM, roofMesh:rfM, labelSprite:lSp, rotY:0};
  roomMeshes.push(entry);
  return entry;
}

function removeRoomMeshes(entry) {
  [entry.mesh,entry.edgeMesh,entry.labelSprite].forEach(o=>{if(o)scene.remove(o);});
  if(entry.roofMesh){
    if(entry.roofMesh._isLRoof) scene.remove(entry.roofMesh);
    else scene.remove(entry.roofMesh);
  }
  roomMeshes.splice(roomMeshes.indexOf(entry),1);
}

function rebuildRoomMeshes(entry) {
  const {fi,ri,room} = entry;
  const oldPos = entry.mesh.position.clone();
  const oldRotY = entry.rotY||0;
  removeRoomMeshes(entry);
  const ne = buildRoomMeshes(room,fi,ri);
  ne.rotY = oldRotY;
  [ne.mesh,ne.edgeMesh].forEach(o=>{if(o)o.rotation.y=oldRotY;});
  // reapply opacity
  const isAct = fi===activeFloor;
  [ne.mesh,ne.edgeMesh].forEach(o=>{if(o&&o.material){o.material.transparent=true;o.material.opacity=isAct?0.88:0.08;o.material.needsUpdate=true;}});
  setRoofOpacity(ne.roofMesh, isAct?0.07:0.02);
  if(ne.labelSprite) ne.labelSprite.material.opacity=isAct?1:0.08;
  return ne;
}

function rebuildRoomList() {
  const el = document.getElementById('roomList');
  if(!FLOORS[activeFloor]){ el.innerHTML=''; return; }
  const rooms = FLOORS[activeFloor].rooms;
  if(!rooms.length){ el.innerHTML='<div style="padding:12px;font-family:Space Mono,monospace;font-size:9px;color:var(--muted);text-align:center">No rooms on this floor</div>'; return; }
  el.innerHTML = rooms.map((r,i)=>`
    <div class="rm-item${selEntry&&selEntry.ri===i&&selEntry.fi===activeFloor?' sel':''}" onclick="selectRoomByIndex(${i})">
      <div class="rm-dot" style="background:${r.color}"></div>
      <span class="rm-name">${r.name}</span>
      <span class="rm-sz">${r.w}×${r.d}</span>
    </div>`).join('');
}

function updateEmptyState() {
  const total = roomMeshes.length;
  document.getElementById('emptyState').style.display = total===0 ? 'block' : 'none';
}

// ═══════════════════════════════
// VIEW MODES
// ═══════════════════════════════
function setView(v) {
  currentView = v;
  ['3d','floor','grid'].forEach(id=>{
    document.getElementById('btn'+id.charAt(0).toUpperCase()+id.slice(1)).className = 'tb-btn' + (v===id?' active':'');
  });
  const gc = document.getElementById('grid-canvas');
  const gcCtrl = document.getElementById('gridControls');
  if(v==='grid'){
    gc.style.display='block'; gcCtrl.style.display='block';
    gc.style.pointerEvents='all';
    bindGridEvents(); resizeGridCanvas(); drawGridCanvas();
    document.getElementById('btnGrid').className='tb-btn active-cyan';
  } else {
    gc.style.display='none'; gcCtrl.style.display='none';
    gc.style.pointerEvents='none'; unbindGridEvents();
  }
  if(v==='floor'){
    floorGrps.forEach((fg,i)=>{fg.grp.visible=(i===activeFloor);});
    roomMeshes.forEach(e=>{
      const s=e.fi===activeFloor;
      [e.mesh,e.edgeMesh,e.labelSprite].forEach(o=>{if(o)o.visible=s;});
      setRoofVisible(e.roofMesh, s);
    });
    // Hide stairs that don't belong to the active floor
    staircases.forEach(sc=>{
      const grp=stairMeshGrps.get(sc.id);
      if(grp) grp.visible=(sc.fi===activeFloor);
    });
    sph.phi=0.02; sph.r=1100; camUp();
  } else if(v==='3d'){
    floorGrps.forEach(fg=>fg.grp.visible=true);
    roomMeshes.forEach(e=>{
      [e.mesh,e.edgeMesh,e.labelSprite].forEach(o=>{if(o)o.visible=true;});
      setRoofVisible(e.roofMesh, true);
    });
    // Show all stairs in 3D mode
    stairMeshGrps.forEach(grp=>{ grp.visible=true; });
    sph = {theta:-0.55,phi:0.72,r:1600}; tgt.set(BW/2,FLOORS.length*FH/2||0,BD/2); camUp();
  }
}

// ═══════════════════════════════
// CANVAS EVENTS 3D
// ═══════════════════════════════
const c3d = document.getElementById('c3d');
function getMouse(e){ const r=c3d.getBoundingClientRect(); mouse.x=((e.clientX-r.left)/r.width)*2-1; mouse.y=-((e.clientY-r.top)/r.height)*2+1; }

c3d.addEventListener('mousedown', e=>{
  if(currentView==='grid') return;
  if(e.button===0){
    if(drawMode){ startDraw(e); return; }
    if(slabMode){ tryPickSlab(e); return; }
    if(moveTool||selectTool){
      const pickedStair = tryPickStair(e);
      if(pickedStair){
        dragging = false; // stair picked — don't orbit
        return;
      }
      // Nothing stair-related — deselect any stair
      deselectStair();
      tryPickRoom(e);
      if(moveTool&&selEntry) startRoomDrag(e);
    }
    dragging=true;
  }
  if(e.button===2) rDragging=true;
  lm={x:e.clientX,y:e.clientY};
});
c3d.addEventListener('mousemove', e=>{
  if(currentView==='grid') return;
  const dx=e.clientX-lm.x, dy=e.clientY-lm.y; lm={x:e.clientX,y:e.clientY};
  if(drawMode&&isDrawing){ updateDraw(e); return; }
  if(stairDragging){ doStairDrag(e); return; }
  if(roomDragging){ doRoomDrag(e); return; }
  if(dragging){ sph.theta-=dx*.004; sph.phi=Math.max(.01,Math.min(Math.PI-.01,sph.phi+dy*.004)); camUp(); }
  if(rDragging){ const sp=sph.r*.0007; const r=new THREE.Vector3().crossVectors(new THREE.Vector3().subVectors(tgt,camera.position).normalize(),new THREE.Vector3(0,1,0)).normalize(); tgt.addScaledVector(r,-dx*sp); tgt.y+=dy*sp*.4; camUp(); }
  doHover(e);
});
c3d.addEventListener('mouseup', e=>{
  if(currentView==='grid') return;
  if(drawMode&&isDrawing){ finishDraw(e); return; }
  if(stairDragging){ endStairDrag(); return; }
  if(roomDragging) endRoomDrag();
  dragging=false; rDragging=false;
});
c3d.addEventListener('mouseleave',()=>{ dragging=false; rDragging=false; endRoomDrag(); endStairDrag(); });
c3d.addEventListener('wheel', e=>{
  if(currentView==='grid') return;
  e.preventDefault();
  const f=new THREE.Vector3().subVectors(tgt,camera.position).normalize();
  const d=sph.r*0.0012*e.deltaY; tgt.addScaledVector(f,-d); sph.r=Math.max(50,sph.r+d); camUp();
},{passive:false});
c3d.addEventListener('contextmenu',e=>e.preventDefault());
c3d.addEventListener('click',e=>{ if(!drawMode&&!roomDragging&&currentView!=='grid') doClick(e); });

window.addEventListener('keydown', e=>{
  if(document.activeElement.tagName==='INPUT'||document.activeElement.tagName==='TEXTAREA') return;
  if((e.ctrlKey||e.metaKey) && e.key==='z'){ e.preventDefault(); undoAction(); return; }
  if((e.ctrlKey||e.metaKey) && (e.key==='y'||e.key==='Z')){ e.preventDefault(); redoAction(); return; }
  if((e.ctrlKey||e.metaKey) && e.key==='d'){ e.preventDefault(); duplicateRoom(); return; }
  if(e.key==='Escape'&&drawMode){ toggleDraw(); return; }
  if((e.key==='Delete'||e.key==='Backspace')&&selStair){ e.preventDefault(); deleteSelStair(); return; }
  if((e.key==='Delete'||e.key==='Backspace')&&selEntry){ e.preventDefault(); deleteSelRoom(); return; }
  if(e.key==='q'||e.key==='Q'){ rotateRoom(-45); return; }
  if(e.key==='e'||e.key==='E'){ rotateRoom(45); return; }
  const ORBIT=0.06, PAN=30;
  if(['ArrowLeft','ArrowRight','ArrowUp','ArrowDown'].includes(e.key)){
    e.preventDefault();
    if(e.shiftKey){
      const cf=new THREE.Vector3().subVectors(tgt,camera.position).setY(0).normalize();
      const cr=new THREE.Vector3().crossVectors(cf,new THREE.Vector3(0,1,0)).normalize();
      if(e.key==='ArrowUp') tgt.addScaledVector(cf,PAN);
      if(e.key==='ArrowDown') tgt.addScaledVector(cf,-PAN);
      if(e.key==='ArrowRight') tgt.addScaledVector(cr,PAN);
      if(e.key==='ArrowLeft') tgt.addScaledVector(cr,-PAN);
    } else if(selStair&&moveTool){
      // Nudge stair with arrow keys
      const step=15;
      const grp=stairMeshGrps.get(selStair.id);
      if(grp){
        if(e.key==='ArrowLeft')  { grp.position.x-=step; selStair.x-=step; }
        if(e.key==='ArrowRight') { grp.position.x+=step; selStair.x+=step; }
        if(e.key==='ArrowUp')    { grp.position.z-=step; selStair.z-=step; }
        if(e.key==='ArrowDown')  { grp.position.z+=step; selStair.z+=step; }
        autoSave(); if(currentView==='grid') drawGridCanvas();
      }
    } else if(selEntry&&moveTool){
      nudgeRoom(e.key==='ArrowRight'?15:e.key==='ArrowLeft'?-15:0, e.key==='ArrowDown'?15:e.key==='ArrowUp'?-15:0);
    } else {
      if(e.key==='ArrowLeft') sph.theta-=ORBIT;
      if(e.key==='ArrowRight') sph.theta+=ORBIT;
      if(e.key==='ArrowUp') sph.phi=Math.max(.01,sph.phi-ORBIT);
      if(e.key==='ArrowDown') sph.phi=Math.min(Math.PI-.01,sph.phi+ORBIT);
    }
    camUp();
  }
});

// ═══════════════════════════════
// ROOM PICKING & HOVER
// ═══════════════════════════════
function getActiveMeshes(){ return roomMeshes.filter(e=>e.fi===activeFloor).map(e=>e.mesh); }

function doHover(e) {
  getMouse(e);
  raycaster.setFromCamera(mouse,camera);
  const hits = raycaster.intersectObjects(getActiveMeshes());
  const nh = hits.length ? roomMeshes.find(e=>e.mesh===hits[0].object) : null;
  if(nh!==hovEntry){
    if(hovEntry&&hovEntry!==selEntry){ hovEntry.mesh.material.emissive?.setHex(0); hovEntry.mesh.material.emissiveIntensity=0; }
    if(nh&&nh!==selEntry){ nh.mesh.material.emissive?.setHex(0xffffff); nh.mesh.material.emissiveIntensity=0.12; }
    hovEntry=nh;
    c3d.style.cursor = nh ? (moveTool?'move':'pointer') : drawMode?'crosshair':'grab';
  }
}

function tryPickRoom(e) {
  getMouse(e);
  raycaster.setFromCamera(mouse,camera);
  const hits = raycaster.intersectObjects(getActiveMeshes());
  if(hits.length){
    const entry = roomMeshes.find(e=>e.mesh===hits[0].object);
    if(entry) selectRoom(entry);
  } else {
    deselectRoom();
  }
}

function doClick(e) {
  // mousedown already handles stair picking; just handle room selection + deselection here
  getMouse(e);
  raycaster.setFromCamera(mouse,camera);
  // Check stairs first
  const allGrps = [];
  for (const [id, grp] of stairMeshGrps) allGrps.push(grp);
  if (allGrps.length) {
    const stairHits = raycaster.intersectObjects(allGrps, true);
    if (stairHits.length) return; // stair click handled by mousedown
  }
  // Check rooms
  const hits = raycaster.intersectObjects(getActiveMeshes());
  if(hits.length){
    const en=roomMeshes.find(e=>e.mesh===hits[0].object);
    if(en){ deselectStair(); selectRoom(en); return; }
  }
  deselectRoom();
  deselectStair();
}

function selectRoom(entry) {
  if(selEntry&&selEntry!==entry){ deselectRoom(false); }
  selEntry=entry;
  entry.mesh.material.emissive?.setHex(0xe8ff3c);
  entry.mesh.material.emissiveIntensity=0.2;
  document.getElementById('rtLabel').textContent=entry.room.name;
  document.getElementById('rtRot').textContent=Math.round(THREE.MathUtils.radToDeg(entry.rotY||0))+'°';
  document.getElementById('roomToolbar').classList.add('show');
  rebuildRoomList();
  setStatus(entry.room.name+' — '+entry.room.w+'×'+entry.room.d);
}

function deselectRoom(refresh=true) {
  if(selEntry){ selEntry.mesh.material.emissive?.setHex(0); selEntry.mesh.material.emissiveIntensity=0; selEntry=null; }
  document.getElementById('roomToolbar').classList.remove('show');
  closeRoomEditPanel();
  if(refresh) rebuildRoomList();
}

function deselectStair() {
  if (selStair) { _highlightStair(selStair, false); selStair = null; }
  document.getElementById('stairToolbar').style.display = 'none';
  closeStairEditPanel();
}

function selectRoomByIndex(ri) {
  const e = roomMeshes.find(m=>m.fi===activeFloor&&m.ri===ri);
  if(e) selectRoom(e);
}

// ═══════════════════════════════
// ROOM DRAGGING
// ═══════════════════════════════
// Move a roofMesh which may be a THREE.Group (L-shape) or a regular Mesh
function moveRoofMesh(roofMesh, dx, dz) {
  if (!roofMesh) return;
  if (roofMesh._isLRoof) {
    roofMesh.children.forEach(c => { c.position.x += dx; c.position.z += dz; });
  } else {
    roofMesh.position.x += dx; roofMesh.position.z += dz;
  }
}

function startRoomDrag(e) {
  if(!selEntry) return;
  getMouse(e); raycaster.setFromCamera(mouse,camera);
  const rY = selEntry.mesh.position.y;
  dragPlane.constant=-rY;
  if(raycaster.ray.intersectPlane(dragPlane,dragIntersect)){
    // For L-shape, mesh position IS the corner; for box it's the center
    dragOffset.set(dragIntersect.x-selEntry.mesh.position.x, 0, dragIntersect.z-selEntry.mesh.position.z);
    roomDragging=true;
  }
}
function doRoomDrag(e) {
  if(!roomDragging||!selEntry) return;
  getMouse(e); raycaster.setFromCamera(mouse,camera);
  const rY=selEntry.mesh.position.y; dragPlane.constant=-rY;
  if(raycaster.ray.intersectPlane(dragPlane,dragIntersect)){
    const isL = selEntry.room.shape==='L';
    let nx, nz, dx, dz;
    if(isL){
      // For L-shape the mesh origin IS the corner (room.x, room.z)
      nx = Math.max(0, Math.min(BW-selEntry.room.w, dragIntersect.x - dragOffset.x));
      nz = Math.max(0, Math.min(BD-selEntry.room.d, dragIntersect.z - dragOffset.z));
      dx = nx - selEntry.mesh.position.x;
      dz = nz - selEntry.mesh.position.z;
      selEntry.room.x = nx;
      selEntry.room.z = nz;
    } else {
      const hw=selEntry.room.w/2, hd=selEntry.room.d/2;
      nx=Math.max(hw,Math.min(BW-hw, dragIntersect.x-dragOffset.x));
      nz=Math.max(hd,Math.min(BD-hd, dragIntersect.z-dragOffset.z));
      dx=nx-selEntry.mesh.position.x; dz=nz-selEntry.mesh.position.z;
      selEntry.room.x=nx-hw; selEntry.room.z=nz-hd;
    }
    [selEntry.mesh,selEntry.edgeMesh,selEntry.labelSprite].forEach(o=>{if(o){o.position.x+=dx;o.position.z+=dz;}});
    moveRoofMesh(selEntry.roofMesh, dx, dz);
  }
}
function endRoomDrag(){ roomDragging=false; if(selEntry) refreshOverlapState(); }
function nudgeRoom(dx,dz){
  if(!selEntry) return;
  const isL = selEntry.room.shape==='L';
  let ddx, ddz;
  if(isL){
    const nx=Math.max(0,Math.min(BW-selEntry.room.w,selEntry.mesh.position.x+dx));
    const nz=Math.max(0,Math.min(BD-selEntry.room.d,selEntry.mesh.position.z+dz));
    ddx=nx-selEntry.mesh.position.x; ddz=nz-selEntry.mesh.position.z;
    selEntry.room.x=nx; selEntry.room.z=nz;
  } else {
    const hw=selEntry.room.w/2, hd=selEntry.room.d/2;
    const nx=Math.max(hw,Math.min(BW-hw,selEntry.mesh.position.x+dx));
    const nz=Math.max(hd,Math.min(BD-hd,selEntry.mesh.position.z+dz));
    ddx=nx-selEntry.mesh.position.x; ddz=nz-selEntry.mesh.position.z;
    selEntry.room.x=nx-hw; selEntry.room.z=nz-hd;
  }
  [selEntry.mesh,selEntry.edgeMesh,selEntry.labelSprite].forEach(o=>{if(o){o.position.x+=ddx;o.position.z+=ddz;}});
  moveRoofMesh(selEntry.roofMesh, ddx, ddz);
}

// ═══════════════════════════════
// TOOL BUTTONS
// ═══════════════════════════════
function setRoomTool(t){
  document.getElementById('rtMove').classList.toggle('act', t==='move');
  moveTool = (t==='move');
  c3d.style.cursor = moveTool ? 'move' : 'pointer';
}
function toggleMoveMode(){ moveTool=!moveTool; selectTool=true; drawMode&&toggleDraw(); document.getElementById('btnMoveMode').className='tb-btn'+(moveTool?' active-cyan':''); setStatus(moveTool?'Move mode — drag rooms':''); if(!moveTool)setRoomTool('select'); }
function toggleSelectMode(){ selectTool=true; moveTool=false; drawMode&&toggleDraw(); document.getElementById('btnSelectMode').className='tb-btn active-cyan'; document.getElementById('btnMoveMode').className='tb-btn'; setStatus('Select mode'); }
function rotateRoom(deg){
  if(!selEntry) return;
  selEntry.rotY=(selEntry.rotY||0)+THREE.MathUtils.degToRad(deg);
  [selEntry.mesh,selEntry.edgeMesh].forEach(o=>{if(o)o.rotation.y=selEntry.rotY;});
  if(selEntry.roofMesh){
    if(selEntry.roofMesh._isLRoof) selEntry.roofMesh.children.forEach(c=>{ c.rotation.y=selEntry.rotY; });
    else selEntry.roofMesh.rotation.y=selEntry.rotY;
  }
  document.getElementById('rtRot').textContent=Math.round(THREE.MathUtils.radToDeg(selEntry.rotY)%360)+'°';
}
function deleteSelRoom(){
  if(!selEntry) return;
  if(!confirm(`Delete "${selEntry.room.name}"?`)) return;
  const fi=selEntry.fi, ri=selEntry.ri;
  removeRoomMeshes(selEntry);
  FLOORS[fi].rooms.splice(ri,1);
  roomMeshes.filter(e=>e.fi===fi&&e.ri>ri).forEach(e=>e.ri--);
  selEntry=null;
  document.getElementById('roomToolbar').classList.remove('show');
  closeRoomEditPanel();
  rebuildRoomList();
  updateEmptyState();
  toast('🗑 Room deleted');
  if(currentView==='grid') drawGridCanvas();
  refreshOverlapState();
}

// ═══════════════════════════════
// ROOM EDIT PANEL
// ═══════════════════════════════
(function initColors(){
  const el=document.getElementById('repColors');
  COLORS.forEach(c=>{
    const s=document.createElement('div'); s.className='re-swatch'; s.style.background=c;
    s.onclick=()=>{document.querySelectorAll('.re-swatch').forEach(x=>x.classList.remove('sel'));s.classList.add('sel');};
    el.appendChild(s);
  });
})();

function openRoomEditPanel(){
  if(!selEntry) return;
  const r=selEntry.room;
  document.getElementById('repName').textContent=r.name;
  document.getElementById('repNameInput').value=r.name;
  document.getElementById('repW').value=r.w; document.getElementById('repWn').value=r.w;
  document.getElementById('repD').value=r.d; document.getElementById('repDn').value=r.d;
  document.getElementById('repH').value=r.h||60; document.getElementById('repHn').value=r.h||60;
  document.querySelectorAll('.re-swatch').forEach((s,i)=>s.classList.toggle('sel',COLORS[i]===r.color));
  const isL = r.shape==='L';
  document.getElementById('repLRow').style.display = isL ? '' : 'none';
  document.getElementById('repLDRow').style.display = isL ? '' : 'none';
  document.getElementById('repLOrientRow').style.display = isL ? '' : 'none';
  if(isL){
    const lwPct=Math.round((r.lw||0.5)*100), ldPct=Math.round((r.ld||0.5)*100);
    document.getElementById('repLW').value=lwPct; document.getElementById('repLWn').value=lwPct;
    document.getElementById('repLD').value=ldPct; document.getElementById('repLDn').value=ldPct;
    // highlight current orientation
    ['TR','TL','BR','BL'].forEach(o=>{
      document.getElementById('lor'+o).classList.toggle('sel', (r.lorient||'TR')===o);
    });
  }
  document.getElementById('roomEditPanel').classList.add('show');
}
function setLOrient(o){
  ['TR','TL','BR','BL'].forEach(id=>document.getElementById('lor'+id).classList.toggle('sel',id===o));
}
function closeRoomEditPanel(){ document.getElementById('roomEditPanel').classList.remove('show'); }
function applyRoomEdit(){
  if(!selEntry) return;
  const oldPos=selEntry.mesh.position.clone(), oldRotY=selEntry.rotY||0;
  const r=selEntry.room;
  r.name=document.getElementById('repNameInput').value||r.name;
  r.w=Math.max(20,parseInt(document.getElementById('repWn').value)||r.w);
  r.d=Math.max(20,parseInt(document.getElementById('repDn').value)||r.d);
  r.h=Math.max(20,parseInt(document.getElementById('repHn').value)||60);
  const sel=document.querySelector('.re-swatch.sel'); if(sel) r.color=sel.style.background;
  if(r.shape==='L'){
    r.lw = Math.max(0.1, Math.min(0.8, parseInt(document.getElementById('repLWn').value)/100));
    r.ld = Math.max(0.1, Math.min(0.8, parseInt(document.getElementById('repLDn').value)/100));
    const selOrient = ['TR','TL','BR','BL'].find(o=>document.getElementById('lor'+o).classList.contains('sel'));
    r.lorient = selOrient || r.lorient || 'TR';
    r.x=oldPos.x; r.z=oldPos.z;
  } else {
    r.x=oldPos.x-r.w/2; r.z=oldPos.z-r.d/2;
  }
  const ne=rebuildRoomMeshes(selEntry);
  selEntry=ne; selEntry.rotY=oldRotY;
  selectRoom(ne); rebuildRoomList(); updateEmptyState();
  if(currentView==='grid') drawGridCanvas();
  toast('✓ Room updated');
  refreshOverlapState();
}

// ═══════════════════════════════
// DRAW MODE
// ═══════════════════════════════
function toggleDraw(){
  drawMode=!drawMode;
  document.getElementById('btnDraw').className='tb-btn'+(drawMode?' active-green':'');
  document.getElementById('drawToolbar').classList.toggle('show',drawMode);
  document.getElementById('typePalette').classList.toggle('show',drawMode);
  if(drawMode){
    if(FLOORS.length===0){ toast('⚠ Add a floor first'); drawMode=false; document.getElementById('btnDraw').className='tb-btn'; document.getElementById('drawToolbar').classList.remove('show'); document.getElementById('typePalette').classList.remove('show'); return; }
    buildTypePalette(); c3d.style.cursor='crosshair';
    setStatus('Draw mode — click and drag to place room');
  } else {
    c3d.style.cursor='grab'; isDrawing=false; drawStart=null; drawEnd=null;
    document.getElementById('drawGhost').style.display='none';
    document.getElementById('measureBadge').style.display='none';
    setStatus('');
  }
}

function buildTypePalette(){
  const pal=document.getElementById('typePalette');
  pal.innerHTML='<div style="width:100%;font-family:Space Mono,monospace;font-size:7px;letter-spacing:2px;color:var(--muted);text-transform:uppercase;margin-bottom:4px;text-align:center">Choose room type</div>';
  TYPES.forEach((rt,i)=>{
    const el=document.createElement('div');
    el.className='tp-item'+(i===0?' sel':'');
    el.innerHTML=`<div class="tp-dot" style="background:${rt.color}"></div>${rt.icon} ${rt.name}`;
    el.onclick=()=>{
      document.querySelectorAll('.tp-item').forEach(x=>x.classList.remove('sel'));
      el.classList.add('sel'); selType=rt;
      document.getElementById('drawTypeLabel').textContent=rt.name;
    };
    pal.appendChild(el);
  });
  selType=TYPES[0];
  document.getElementById('drawTypeLabel').textContent=selType.name;
}

const _drawPickPlane = new THREE.Plane(new THREE.Vector3(0,1,0),0);
const _drawIntersect = new THREE.Vector3();
function getWorldFloor(e){
  getMouse(e); raycaster.setFromCamera(mouse,camera);
  const fY = activeFloor*FH+SLAB; _drawPickPlane.constant=-fY;
  if(raycaster.ray.intersectPlane(_drawPickPlane,_drawIntersect)) return {x:_drawIntersect.x, z:_drawIntersect.z};
  return null;
}
function worldToScreen(wx,wz){
  const v=new THREE.Vector3(wx, activeFloor*FH+SLAB, wz); v.project(camera);
  const wr=document.getElementById('wrap').getBoundingClientRect();
  return {x:(v.x+1)/2*wr.width, y:(-v.y+1)/2*wr.height};
}
function startDraw(e){ if(e.button!==0) return; isDrawing=true; drawStart=getWorldFloor(e); drawEnd=drawStart?{...drawStart}:null; }
function updateDraw(e){
  if(!isDrawing) return; drawEnd=getWorldFloor(e); if(!drawStart||!drawEnd) return;
  const s1=worldToScreen(drawStart.x,drawStart.z), s2=worldToScreen(drawEnd.x,drawEnd.z);
  const gx=Math.min(s1.x,s2.x), gy=Math.min(s1.y,s2.y), gw=Math.abs(s2.x-s1.x), gh=Math.abs(s2.y-s1.y);
  const dg=document.getElementById('drawGhost');
  dg.style.display='block'; dg.style.left=gx+'px'; dg.style.top=gy+'px'; dg.style.width=gw+'px'; dg.style.height=gh+'px';
  dg.style.borderColor=selType.color; dg.style.background=selType.color+'15';
  const ww=Math.round(Math.abs(drawEnd.x-drawStart.x)), wd=Math.round(Math.abs(drawEnd.z-drawStart.z));
  const mb=document.getElementById('measureBadge'); mb.style.display='block'; mb.textContent=`${ww} × ${wd}`;
}
function finishDraw(e){
  isDrawing=false; drawEnd=getWorldFloor(e);
  document.getElementById('drawGhost').style.display='none'; document.getElementById('measureBadge').style.display='none';
  if(!drawStart||!drawEnd) return;
  const x=Math.min(drawStart.x,drawEnd.x), z=Math.min(drawStart.z,drawEnd.z);
  const w=Math.abs(drawEnd.x-drawStart.x), d=Math.abs(drawEnd.z-drawStart.z);
  if(w<20||d<20){ toast('⚠ Room too small — drag larger'); return; }
  let name=selType.name;
  if(name==='Custom') name=prompt('Room name?')||'Room';
  const fi=activeFloor;
  const room={name, x, z, w, d, h:60, color:selType.color};
  if(selType.shape==='L'){ room.shape='L'; room.lw=0.5; room.ld=0.5; room.lorient='TR'; }
  FLOORS[fi].rooms.push(room);
  const ri=FLOORS[fi].rooms.length-1;
  const entry=buildRoomMeshes(room,fi,ri);
  selectRoom(entry); rebuildRoomList(); refreshFloorUI(); updateEmptyState();
  if(currentView==='grid') drawGridCanvas();
  autoSave(); toast(`✓ ${name} placed`);
  drawStart=null; drawEnd=null;
  refreshOverlapState();
}

// Intercept draw events before 3D drag
c3d.addEventListener('mousedown',e=>{ if(drawMode&&e.button===0) startDraw(e); },true);
c3d.addEventListener('mousemove',e=>{ if(drawMode&&isDrawing) updateDraw(e); },true);
c3d.addEventListener('mouseup',e=>{ if(drawMode&&isDrawing) finishDraw(e); },true);

// ═══════════════════════════════
// GRID VIEW
// ═══════════════════════════════
function resizeGridCanvas(){
  const gc=document.getElementById('grid-canvas'), wrap=document.getElementById('wrap');
  gc.width=wrap.clientWidth; gc.height=wrap.clientHeight;
  gridPan={x:gc.width/2-(BW/2)*gridZoom, y:gc.height/2-(BD/2)*gridZoom};
  if(currentView==='grid') drawGridCanvas();
}
function drawGridCanvas(){
  const gc=document.getElementById('grid-canvas'); if(!gc||currentView!=='grid') return;
  gc.width=gc.parentElement.clientWidth; gc.height=gc.parentElement.clientHeight;
  const ctx=gc.getContext('2d'), W=gc.width, H=gc.height;
  const gs=parseInt(document.getElementById('gridSz').value)||25, Z=gridZoom;
  ctx.clearRect(0,0,W,H); ctx.fillStyle='#07080c'; ctx.fillRect(0,0,W,H);
  const sx=((gridPan.x%(gs*Z))+(gs*Z))%(gs*Z), sy=((gridPan.y%(gs*Z))+(gs*Z))%(gs*Z);
  ctx.strokeStyle='rgba(255,255,255,0.035)'; ctx.lineWidth=0.5; ctx.beginPath();
  for(let x=sx;x<W;x+=gs*Z){ctx.moveTo(x,0);ctx.lineTo(x,H);}
  for(let y=sy;y<H;y+=gs*Z){ctx.moveTo(0,y);ctx.lineTo(W,y);}
  ctx.stroke();
  const ms=gs*4, msx=((gridPan.x%(ms*Z))+(ms*Z))%(ms*Z), msy=((gridPan.y%(ms*Z))+(ms*Z))%(ms*Z);
  ctx.strokeStyle='rgba(0,229,255,0.07)'; ctx.lineWidth=1; ctx.beginPath();
  for(let x=msx;x<W;x+=ms*Z){ctx.moveTo(x,0);ctx.lineTo(x,H);}
  for(let y=msy;y<H;y+=ms*Z){ctx.moveTo(0,y);ctx.lineTo(W,y);}
  ctx.stroke();
  const ox=gridPan.x, oy=gridPan.y;
  ctx.strokeStyle='rgba(0,229,255,0.3)'; ctx.lineWidth=1; ctx.setLineDash([4,4]);
  ctx.beginPath(); ctx.moveTo(ox,0); ctx.lineTo(ox,H); ctx.stroke();
  ctx.beginPath(); ctx.moveTo(0,oy); ctx.lineTo(W,oy); ctx.stroke();
  ctx.setLineDash([]);
  ctx.strokeStyle='rgba(0,229,255,0.4)'; ctx.lineWidth=2;
  ctx.strokeRect(gridPan.x,gridPan.y,BW*Z,BD*Z);
  ctx.fillStyle='rgba(0,229,255,0.02)'; ctx.fillRect(gridPan.x,gridPan.y,BW*Z,BD*Z);
  if(FLOORS[activeFloor]){
    FLOORS[activeFloor].rooms.forEach((r,ri)=>{
      const rx=gridPan.x+r.x*Z, ry=gridPan.y+r.z*Z, rw=r.w*Z, rh=r.d*Z;
      const isSel=selEntry&&selEntry.ri===ri&&selEntry.fi===activeFloor;
      ctx.fillStyle=r.color+'44';
      ctx.strokeStyle=isSel?'#e8ff3c':r.color; ctx.lineWidth=isSel?2:1.5;
      if(r.shape==='L'){
        const lw=(r.lw||0.5)*r.w, ld=(r.ld||0.5)*r.d;
        const pts=getLShapePts(r.w, r.d, lw, ld, r.lorient||'TR');
        ctx.beginPath();
        pts.forEach(([px,pz],i)=>{
          const sx=gridPan.x+r.x*Z+px*Z, sy=gridPan.y+r.z*Z+pz*Z;
          i===0 ? ctx.moveTo(sx,sy) : ctx.lineTo(sx,sy);
        });
        ctx.closePath(); ctx.fill(); ctx.stroke();
      } else {
        ctx.fillRect(rx,ry,rw,rh);
        ctx.strokeRect(rx,ry,rw,rh);
      }
      const labelX = r.shape==='L' ? rx+rw*0.35 : rx+rw/2;
      const labelY = r.shape==='L' ? ry+rh*0.35 : ry+rh/2;
      if(rw>35&&rh>20){
        ctx.fillStyle=isSel?'#e8ff3c':r.color;
        ctx.font=`${Math.max(9,Math.min(13,rw/8))}px Barlow,sans-serif`;
        ctx.textAlign='center'; ctx.textBaseline='middle';
        ctx.fillText(r.name.length>14?r.name.slice(0,12)+'…':r.name,labelX,labelY);
        if(rw>60&&rh>30){ctx.fillStyle='rgba(255,255,255,0.3)';ctx.font='8px Space Mono,monospace';ctx.fillText(`${r.w}×${r.d}`,labelX,labelY+12);}
      }
    });
  }
  // Stairs: geometry is centred on X (x=0 locally), front edge at z=0
  // So grp.position.x = centre-X, grp.position.z = front-Z
  staircases.filter(sc=>sc.fi===activeFloor).forEach(sc=>{
    const grp=stairMeshGrps.get(sc.id);
    const wx=grp?grp.position.x:sc.x, wz=grp?grp.position.z:sc.z;
    const rotY=grp?grp.rotation.y:(sc.rotY||0);
    const isSel=selStair&&selStair.id===sc.id;
    const sw=sc.w*Z, sd=sc.d*Z;
    const ccx=gridPan.x+wx*Z, ccy=gridPan.y+wz*Z+sd/2;
    ctx.save();
    ctx.translate(ccx,ccy); ctx.rotate(-rotY); ctx.translate(-sw/2,-sd/2);
    ctx.fillStyle=isSel?'rgba(234,179,8,0.20)':'rgba(136,153,187,0.15)'; ctx.fillRect(0,0,sw,sd);
    ctx.strokeStyle=isSel?'#eab308':'#8899bb'; ctx.lineWidth=isSel?2.5:1.5; ctx.strokeRect(0,0,sw,sd);
    ctx.strokeStyle=isSel?'rgba(234,179,8,0.35)':'rgba(136,153,187,0.30)'; ctx.lineWidth=0.7;
    const hg=Math.max(6,sd/6);
    for(let hx=-sd;hx<sw+sd;hx+=hg){ctx.beginPath();ctx.moveTo(hx,0);ctx.lineTo(hx+sd,sd);ctx.stroke();}
    const nSteps=Math.max(3,Math.round(sc.d/18));
    ctx.strokeStyle=isSel?'rgba(234,179,8,0.55)':'rgba(200,210,230,0.45)'; ctx.lineWidth=0.9;
    for(let i=1;i<nSteps;i++){const ty=(sd/nSteps)*i;ctx.beginPath();ctx.moveTo(0,ty);ctx.lineTo(sw,ty);ctx.stroke();}
    ctx.strokeStyle=isSel?'#eab308':'#aabbcc'; ctx.lineWidth=1.5;
    const ax=sw/2,ay1=sd*0.78,ay2=sd*0.15;
    ctx.beginPath();ctx.moveTo(ax,ay1);ctx.lineTo(ax,ay2);ctx.stroke();
    const ah=Math.min(sw*0.15,9);
    ctx.beginPath();ctx.moveTo(ax-ah,ay2+ah*1.3);ctx.lineTo(ax,ay2);ctx.lineTo(ax+ah,ay2+ah*1.3);ctx.stroke();
    if(sw>30&&sd>20){ctx.fillStyle=isSel?'#eab308':'#aabbcc';ctx.font=`bold ${Math.max(8,Math.min(11,sw/5))}px Barlow,sans-serif`;ctx.textAlign='center';ctx.textBaseline='middle';ctx.fillText(sc.name.length>12?sc.name.slice(0,10)+'…':sc.name,sw/2,sd/2);}
    ctx.restore();
  });
  ctx.fillStyle='rgba(0,229,255,0.6)'; ctx.font='bold 11px Barlow,sans-serif'; ctx.textAlign='left'; ctx.textBaseline='top';
  ctx.fillText(`${FLOORS[activeFloor]?.label||'No floor'}  ·  Grid ${gs}px  ·  ${Math.round(Z*100)}%`,12,12);
}

let gridEvBound=false;
function onGMouseDown(e){ if(e.button===1||e.button===2||e.altKey){gridDragging=true;gridDragStart={x:e.clientX,y:e.clientY};gridPanStart={...gridPan};e.preventDefault();} }
function onGMouseMove(e){
  if(!gridEvBound) return;
  const gc=document.getElementById('grid-canvas'), r=gc.getBoundingClientRect();
  const sx=e.clientX-r.left, sy=e.clientY-r.top;
  const wx=Math.round((sx-gridPan.x)/gridZoom), wy=Math.round((sy-gridPan.y)/gridZoom);
  document.getElementById('cursorPos').textContent=`${wx}, ${wy}`;
  if(gridDragging){gridPan.x=gridPanStart.x+(e.clientX-gridDragStart.x);gridPan.y=gridPanStart.y+(e.clientY-gridDragStart.y);drawGridCanvas();}
}
function onGMouseUp(){gridDragging=false;}
function onGWheel(e){
  e.preventDefault();
  const gc=document.getElementById('grid-canvas'), r=gc.getBoundingClientRect();
  const mx=e.clientX-r.left, my=e.clientY-r.top;
  const nz=Math.max(0.15,Math.min(10,gridZoom*(e.deltaY<0?1.12:0.89)));
  gridPan.x=mx-(mx-gridPan.x)*(nz/gridZoom); gridPan.y=my-(my-gridPan.y)*(nz/gridZoom); gridZoom=nz; drawGridCanvas();
}
function bindGridEvents(){
  if(gridEvBound) return; gridEvBound=true;
  const gc=document.getElementById('grid-canvas');
  gc.addEventListener('mousedown',onGMouseDown); gc.addEventListener('mousemove',onGMouseMove);
  gc.addEventListener('mouseup',onGMouseUp); gc.addEventListener('wheel',onGWheel,{passive:false});
  gc.addEventListener('contextmenu',e=>e.preventDefault());
  window.addEventListener('resize',resizeGridCanvas);
}
function unbindGridEvents(){
  if(!gridEvBound) return; gridEvBound=false;
  const gc=document.getElementById('grid-canvas');
  gc.removeEventListener('mousedown',onGMouseDown); gc.removeEventListener('mousemove',onGMouseMove);
  gc.removeEventListener('mouseup',onGMouseUp); gc.removeEventListener('wheel',onGWheel);
  window.removeEventListener('resize',resizeGridCanvas);
}

// ═══════════════════════════════
// CAMERA
// ═══════════════════════════════
function resetView(){ sph={theta:-0.55,phi:0.72,r:1600}; tgt.set(BW/2,FLOORS.length*FH/2||0,BD/2); camUp(); }
function topDown(){ sph.phi=0.05; sph.r=1100; camUp(); }

// ═══════════════════════════════
// DATABASE
// ═══════════════════════════════
// ═══════════════════════════════
// DB — MySQL via PHP API
// ═══════════════════════════════
const AS_KEY='archform_autosave';
async function lsGet(k){ try{return localStorage.getItem(k);}catch(e){return null;} }
async function lsSet(k,v){ try{localStorage.setItem(k,v);return true;}catch(e){return false;} }
async function lsDel(k){ try{localStorage.removeItem(k);}catch(e){} }

function snapshot(){
  return {
    version:1, savedAt:new Date().toISOString(),
    BW, BD, FH,
    staircases: staircases.map(s=>{
      const grp=stairMeshGrps.get(s.id);
      return {...s, x:grp?grp.position.x:s.x, z:grp?grp.position.z:s.z, rotY:grp?grp.rotation.y:(s.rotY||0)};
    }),
    floors:FLOORS.map((fl,fi)=>({
      id:fl.id, label:fl.label, short:fl.short, ox:fl.ox||0, oy:fl.oy||0, oz:fl.oz||0,
      rooms:fl.rooms.map((r,ri)=>{
        const e=roomMeshes.find(m=>m.fi===fi&&m.ri===ri);
        const isL = r.shape==='L';
        const sx = e ? (isL ? e.mesh.position.x : e.mesh.position.x - r.w/2) : r.x;
        const sz = e ? (isL ? e.mesh.position.z : e.mesh.position.z - r.d/2) : r.z;
        const base = {name:r.name,color:r.color,h:r.h||60,w:r.w,d:r.d,x:sx,z:sz,rotY:e?e.rotY||0:0};
        if(isL){ base.shape=r.shape; base.lw=r.lw||0.5; base.ld=r.ld||0.5; base.lorient=r.lorient||'TR'; }
        if(r.doors) base.doors=r.doors;
        if(r.windows) base.windows=r.windows;
        return base;
      })
    }))
  };
}

function restoreLayout(snap){
  if(!snap||!snap.floors) return;
  if(snap.BW){BW=snap.BW;document.getElementById('bwInput').value=BW;}
  if(snap.BD){BD=snap.BD;document.getElementById('bdInput').value=BD;}
  if(snap.FH){FH=snap.FH;document.getElementById('fhInput').value=FH;}
  // Remove all existing
  roomMeshes.slice().forEach(e=>{ [e.mesh,e.edgeMesh,e.labelSprite].forEach(o=>{if(o)scene.remove(o);}); if(e.roofMesh){if(e.roofMesh._isLRoof)scene.remove(e.roofMesh);else scene.remove(e.roofMesh);} });
  roomMeshes.length=0;
  floorGrps.slice().forEach(fg=>{ disposeGroup(fg.grp); }); floorGrps.length=0;
  FLOORS.length=0;
  // Rebuild from snap
  snap.floors.forEach((sf,fi)=>{
    const fl={id:sf.id,label:sf.label,short:sf.short,rooms:[],ox:sf.ox||0,oy:sf.oy||0,oz:sf.oz||0};
    FLOORS.push(fl);
    const Y=fi*FH, grp=new THREE.Group();
    const wMat=new THREE.MeshStandardMaterial({color:0x1c2438,roughness:0.55,metalness:0.2});
    const wallH=FH-SLAB, wallY=Y+SLAB+wallH/2;
    const slabM=new THREE.Mesh(new THREE.BoxGeometry(BW+WT*2,SLAB,BD+WT*2),new THREE.MeshStandardMaterial({color:0x141824,roughness:0.7}));
    slabM.position.set(BW/2,Y+SLAB/2,BD/2); slabM.receiveShadow=true; grp.add(slabM);
    [[BW+WT*2,wallH,WT,BW/2,wallY,-WT/2],[BW+WT*2,wallH,WT,BW/2,wallY,BD+WT/2],[WT,wallH,BD,-WT/2,wallY,BD/2],[WT,wallH,BD,BW+WT/2,wallY,BD/2]].forEach(([w,h,d,x,y,z])=>{
      const m=new THREE.Mesh(new THREE.BoxGeometry(w,h,d),wMat.clone()); m.position.set(x,y,z); m.castShadow=true; grp.add(m);
    });
    const bSp=new THREE.Sprite(new THREE.SpriteMaterial({map:makeTex(fl.short,'rgba(232,255,60,1)',36),depthTest:false}));
    bSp.scale.set(50,12,1); bSp.position.set(-WT-22,Y+SLAB+wallH/2,BD/2); grp.add(bSp);
    scene.add(grp); grp.position.set(fl.ox||0, fl.oy||0, fl.oz||0); floorGrps.push({grp,fi,Y,floor:fl});
    (sf.rooms||[]).forEach((sr,ri)=>{
      const room={name:sr.name,color:sr.color,h:sr.h||60,w:sr.w,d:sr.d,x:sr.x,z:sr.z};
      if(sr.shape){ room.shape=sr.shape; room.lw=sr.lw||0.5; room.ld=sr.ld||0.5; room.lorient=sr.lorient||'TR'; }
      fl.rooms.push(room);
      const e=buildRoomMeshes(room,fi,ri);
      e.rotY=sr.rotY||0;
      [e.mesh,e.edgeMesh].forEach(o=>{if(o)o.rotation.y=e.rotY;});
      if(e.roofMesh){if(e.roofMesh._isLRoof)e.roofMesh.children.forEach(c=>{c.rotation.y=e.rotY;});else e.roofMesh.rotation.y=e.rotY;}
    });
  });
  activeFloor=0; activateFloor(0); refreshFloorUI(); updateEmptyState();
  tgt.set(BW/2,FLOORS.length*FH/2||0,BD/2); camUp();
  // Restore staircases — clear ALL stair meshes from scene first
  stairMeshGrps.forEach((g,id) => { disposeGroup(g); });
  stairMeshGrps.clear();
  staircases=[];  _stairId=0;
  if(snap.staircases && snap.staircases.length){
    snap.staircases.forEach(s=>{
      _stairId=Math.max(_stairId,s.id);
      staircases.push({...s});
      const g=buildStairMesh(s); if(g) stairMeshGrps.set(s.id,g);
    });
  }
  refreshStairList();
}

// ═══════════════════════════════
// UNDO / REDO
// ═══════════════════════════════
const undoStack = [];
const redoStack = [];
const MAX_HISTORY = 40;
let _undoPaused = false;

function pushUndo() {
  if (_undoPaused) return;
  try {
    const s = JSON.stringify(snapshot());
    if (undoStack.length && undoStack[undoStack.length-1] === s) return;
    undoStack.push(s);
    if (undoStack.length > MAX_HISTORY) undoStack.shift();
    redoStack.length = 0;
    _updateUndoButtons();
  } catch(e) {}
}

function undoAction() {
  if (undoStack.length < 2) return;
  redoStack.push(undoStack.pop()); // current state → redo
  const prev = undoStack[undoStack.length - 1];
  if (!prev) return;
  _undoPaused = true;
  try { restoreLayout(JSON.parse(prev)); toast('↩ Undo'); } catch(e) {}
  _undoPaused = false;
  _updateUndoButtons();
}

function redoAction() {
  if (!redoStack.length) return;
  const next = redoStack.pop();
  undoStack.push(next);
  _undoPaused = true;
  try { restoreLayout(JSON.parse(next)); toast('↪ Redo'); } catch(e) {}
  _undoPaused = false;
  _updateUndoButtons();
}

function _updateUndoButtons() {
  const u = document.getElementById('btnUndo');
  const r = document.getElementById('btnRedo');
  if (u) u.style.opacity = undoStack.length > 1 ? '1' : '.35';
  if (r) r.style.opacity = redoStack.length ? '1' : '.35';
}

// ═══════════════════════════════
// DUPLICATE ROOM
// ═══════════════════════════════
function duplicateRoom() {
  if (!selEntry) return;
  const fl = FLOORS[selEntry.fi];
  if (!fl) return;
  const src = selEntry.room;
  const newRoom = { name: src.name + ' Copy', color: src.color, h: src.h||60,
    w: src.w, d: src.d, x: src.x + 20, z: src.z + 20 };
  if(src.shape){ newRoom.shape=src.shape; newRoom.lw=src.lw||0.5; newRoom.ld=src.ld||0.5; newRoom.lorient=src.lorient||'TR'; }
  fl.rooms.push(newRoom);
  const ri = fl.rooms.length - 1;
  const ne = buildRoomMeshes(newRoom, selEntry.fi, ri);
  ne.rotY = selEntry.rotY || 0;
  [ne.mesh, ne.edgeMesh].forEach(o => { if(o) o.rotation.y = ne.rotY; });
  if(ne.roofMesh){if(ne.roofMesh._isLRoof)ne.roofMesh.children.forEach(c=>{c.rotation.y=ne.rotY;});else ne.roofMesh.rotation.y=ne.rotY;}
  selectRoom(ne);
  rebuildRoomList();
  autoSave();
  toast('⧉ Room duplicated');
}

// ═══════════════════════════════
// SCREENSHOT
// ═══════════════════════════════
function takeScreenshot() {
  renderer.render(scene, camera);
  const dataURL = renderer.domElement.toDataURL('image/png');
  const a = document.createElement('a');
  a.href = dataURL;
  a.download = 'archform-' + Date.now() + '.png';
  a.click();
  toast('📸 Screenshot saved');
}

// ═══════════════════════════════
// STATS PANEL
// ═══════════════════════════════
function toggleStatsPanel() {
  const panel = document.getElementById('statsPanel');
  const isOpen = panel.style.display === 'block';
  if (!isOpen) { buildStatsContent(); panel.style.display = 'block'; }
  else panel.style.display = 'none';
}

function buildStatsContent() {
  const el = document.getElementById('statsContent');
  let html = '';
  let totalRooms = 0, totalArea = 0;

  FLOORS.forEach((fl, fi) => {
    const floorArea = fl.rooms.reduce((sum, r) => sum + (r.w * r.d), 0);
    totalArea += floorArea;
    totalRooms += fl.rooms.length;
    html += `<div style="color:var(--accent);margin:6px 0 3px;font-size:8px;letter-spacing:1.5px;text-transform:uppercase">${fl.label}</div>`;
    if (!fl.rooms.length) {
      html += `<div style="color:var(--muted);padding-left:8px">No rooms</div>`;
    } else {
      fl.rooms.forEach(r => {
        const a = ((r.w * r.d) / 10000).toFixed(1);
        html += `<div style="display:flex;justify-content:space-between;gap:16px;padding-left:8px">
          <span style="color:var(--text)">${r.name}</span>
          <span>${r.w}×${r.d} <span style="color:#a78bfa">${a}m²</span></span>
        </div>`;
      });
      html += `<div style="display:flex;justify-content:space-between;padding-left:8px;margin-top:2px;border-top:1px solid var(--border);padding-top:3px">
        <span style="color:var(--muted2)">${fl.rooms.length} room${fl.rooms.length!==1?'s':''}</span>
        <span style="color:#a78bfa">${(floorArea/10000).toFixed(1)}m²</span>
      </div>`;
    }
  });

  // Stairs count
  const stairCount = staircases.length;

  html += `<div style="border-top:1px solid var(--border2);margin-top:8px;padding-top:8px;display:flex;flex-direction:column;gap:3px">
    <div style="display:flex;justify-content:space-between"><span style="color:var(--muted2)">Total Floors</span><span style="color:var(--text)">${FLOORS.length}</span></div>
    <div style="display:flex;justify-content:space-between"><span style="color:var(--muted2)">Total Rooms</span><span style="color:var(--text)">${totalRooms}</span></div>
    <div style="display:flex;justify-content:space-between"><span style="color:var(--muted2)">Total Area</span><span style="color:#a78bfa;font-weight:700">${(totalArea/10000).toFixed(1)}m²</span></div>
    <div style="display:flex;justify-content:space-between"><span style="color:var(--muted2)">Staircases</span><span style="color:var(--text)">${stairCount}</span></div>
  </div>`;

  el.innerHTML = html;
}
async function autoSave(){ try{lsSet(AS_KEY,JSON.stringify(snapshot()));}catch(e){} pushUndo(); }
async function loadAuto(){ const r=await lsGet(AS_KEY); if(!r)return; try{restoreLayout(JSON.parse(r)); toast('✓ Session restored');}catch(e){} }

const API_SAVE='../php/api/admin/floorplan3d_save.php';
const API_LIST='../php/api/admin/floorplan3d_list.php';

function openSaveLoad(){
  document.getElementById('dbPanel').classList.add('show');
  document.getElementById('dbBackdrop').classList.add('show');
  refreshDbList();
}
function closeSaveLoad(){
  document.getElementById('dbPanel').classList.remove('show');
  document.getElementById('dbBackdrop').classList.remove('show');
}
async function refreshDbList(){
  const el=document.getElementById('dbList');
  el.innerHTML='<div class="db-empty">Loading...</div>';
  try{
    const res=await fetch(API_LIST+'?action=list',{credentials:'include'});
    const data=await res.json();
    if(!data.success||!data.layouts.length){el.innerHTML='<div class="db-empty">No saved layouts yet</div>';return;}
    el.innerHTML=data.layouts.map(it=>`
      <div class="db-item">
        <div style="flex:1">
          <div class="db-item-name">${it.name}${it.is_active=='1'?'<span style="margin-left:6px;font-size:8px;background:rgba(232,255,60,.15);color:#e8ff3c;border:1px solid rgba(232,255,60,.3);padding:1px 6px;border-radius:2px">ACTIVE</span>':''}</div>
          <div class="db-item-meta">${new Date(it.updated_at).toLocaleString()} · ${it.saved_by_name||'Admin'}</div>
        </div>
        <div style="display:flex;gap:4px">
          <button class="db-act-btn db-load" onclick="dbLoad(${it.id})">Load</button>
          ${it.is_active!='1'?`<button class="db-act-btn" style="background:rgba(232,255,60,.12);color:#e8ff3c" onclick="dbSetActive(${it.id})">Publish</button>`:'<button class="db-act-btn" style="background:rgba(232,255,60,.05);color:#e8ff3c;opacity:.5" disabled>Published</button>'}
          <button class="db-act-btn db-del" onclick="dbDel(${it.id})">✕</button>
        </div>
      </div>`).join('');
  }catch(e){el.innerHTML='<div class="db-empty" style="color:#ff4d6d">Failed to load layouts</div>';}
}
async function dbSave(){
  let name=document.getElementById('dbName').value.trim();
  if(!name) name='Layout '+new Date().toLocaleTimeString();
  const snap=snapshot(); snap.name=name;
  try{
    const res=await fetch(API_SAVE,{method:'POST',credentials:'include',headers:{'Content-Type':'application/json'},body:JSON.stringify({action:'save',name,layout_json:JSON.stringify(snap)})});
    const data=await res.json();
    if(data.success){ document.getElementById('dbName').value=''; refreshDbList(); toast('💾 Saved "'+name+'" to database'); }
    else toast('⚠ '+data.message);
  }catch(e){toast('⚠ Save failed');}
}
async function dbLoad(id){
  try{
    const res=await fetch(API_LIST+'?action=load&id='+id,{credentials:'include'});
    const data=await res.json();
    if(!data.success){toast('⚠ '+data.message);return;}
    restoreLayout(JSON.parse(data.layout_json));
    lsSet(AS_KEY,JSON.stringify(snapshot()));
    closeSaveLoad(); toast('✓ Layout loaded from database');
  }catch(e){toast('⚠ Load error');}
}
async function dbSetActive(id){
  try{
    const res=await fetch(API_SAVE,{method:'POST',credentials:'include',headers:{'Content-Type':'application/json'},body:JSON.stringify({action:'set_active',id,layout_json:'{}',name:''})});
    const data=await res.json();
    if(data.success){refreshDbList();toast('✓ Layout published — visible to all users');}
    else toast('⚠ '+data.message);
  }catch(e){toast('⚠ Publish failed');}
}
async function dbDel(id){
  if(!confirm('Delete this layout?'))return;
  try{
    const res=await fetch(API_SAVE,{method:'POST',credentials:'include',headers:{'Content-Type':'application/json'},body:JSON.stringify({action:'delete',id,layout_json:'{}',name:''})});
    const data=await res.json();
    if(data.success){refreshDbList();toast('Deleted');}
    else toast('⚠ '+data.message);
  }catch(e){toast('⚠ Delete failed');}
}
function dbExport(){ const snap=snapshot(); snap.name=document.getElementById('dbName').value||'layout'; const a=document.createElement('a'); a.href=URL.createObjectURL(new Blob([JSON.stringify(snap,null,2)],{type:'application/json'})); a.download='archform-'+Date.now()+'.json'; a.click(); toast('⬇ Exported'); }
function dbImportFile(ev){ const f=ev.target.files[0]; if(!f)return; const r=new FileReader(); r.onload=async e=>{try{restoreLayout(JSON.parse(e.target.result)); lsSet(AS_KEY,JSON.stringify(snapshot())); closeSaveLoad(); toast('⬆ Layout imported');}catch(ex){ toast('⚠ Invalid file'); } }; r.readAsText(f); ev.target.value=''; }

// ═══════════════════════════════
// UI HELPERS
// ═══════════════════════════════
function toast(msg){ clearTimeout(toastTimer); const el=document.getElementById('toast'); el.textContent=msg; el.style.display='block'; el.style.animation='none'; void el.offsetWidth; el.style.animation='fadeInOut 2s ease forwards'; toastTimer=setTimeout(()=>el.style.display='none',2100); }
function setStatus(msg){ document.getElementById('statusMsg').textContent=msg; }

// ═══════════════════════════════
// RESIZE + LOOP
// ═══════════════════════════════
function resize(){ const wrap=document.getElementById('wrap'); renderer.setSize(wrap.clientWidth,wrap.clientHeight); camera.aspect=wrap.clientWidth/wrap.clientHeight; camera.updateProjectionMatrix(); }
resize(); window.addEventListener('resize',resize);

const clock = new THREE.Clock();
function animate(){
  requestAnimationFrame(animate);
  const t=clock.getElapsedTime();
  // Pulse selected room
  if(selEntry&&selEntry.mesh.material.emissiveIntensity>0){
    selEntry.mesh.material.emissiveIntensity = 0.15+0.1*Math.sin(t*3);
  }
  renderer.render(scene,camera);
}
animate();

// Init
camUp();
(async()=>{
  await loadAuto();
  if(FLOORS.length===0){ addFloor(); }
  refreshFloorUI();
  rebuildRoomList();
  updateEmptyState();
  setTimeout(()=>{ _undoPaused=false; pushUndo(); _updateUndoButtons(); },200);
})();

// ═══════════════════════════════
// DOOR EDITOR
// ═══════════════════════════════
let doorPanelOpen = false;
let curDoorSide = 'front';
const doorMeshMap = new Map(); // key:"fi-ri-di" -> THREE.Group

function toggleDoorPanel() {
  if (!selEntry) { toast('⚠ Select a room first'); return; }
  doorPanelOpen = !doorPanelOpen;
  if (doorPanelOpen && winPanelOpen) { winPanelOpen=false; document.getElementById('winPanel').classList.remove('show'); selWinIdx=null; }
  document.getElementById('doorPanel').classList.toggle('show', doorPanelOpen);
  document.getElementById('btnDoors').className = 'tb-btn' + (doorPanelOpen ? ' active-red' : '');
  if (doorPanelOpen) refreshDoorPanel();
}

function selDoorSide(side) {
  curDoorSide = side;
  document.querySelectorAll('#dpSides .side-btn').forEach(b => b.classList.toggle('sel', b.dataset.side === side));
}

function refreshDoorPanel() {
  if (!selEntry) return;
  document.getElementById('dpRoomName').textContent = selEntry.room.name;
  const doors = selEntry.room.doors || [];
  const list = document.getElementById('dpList');
  if (!doors.length) { list.innerHTML = '<div class="dp-empty">No doors yet</div>'; return; }
  list.innerHTML = doors.map((d, i) => `
    <div class="dp-list-item">
      <span>🚪</span><span style="color:var(--text)">${d.side}</span>
      <span>offset ${d.offset}% · w${d.width}</span>
      <button class="dp-del" onclick="deleteDoor(${i})">✕</button>
    </div>`).join('');
}

function buildDoorMesh(room, door, fi) {
  const fg = floorGrps.find(g => g.fi === fi);
  if (!fg) return null;
  const Y = fg.Y + SLAB;
  const rH = room.h || 60;
  const dH = rH * ((door.height || 75) / 100);
  const dW = door.width;
  const grp = new THREE.Group();
  const off = door.offset / 100;
  let px = 0, pz = 0, rotY = 0;
  switch (door.side) {
    case 'front': px = room.x + room.w * off; pz = room.z; rotY = 0; break;
    case 'back':  px = room.x + room.w * off; pz = room.z + room.d; rotY = Math.PI; break;
    case 'left':  px = room.x; pz = room.z + room.d * off; rotY = -Math.PI/2; break;
    case 'right': px = room.x + room.w; pz = room.z + room.d * off; rotY = Math.PI/2; break;
  }
  const fMat = new THREE.MeshStandardMaterial({color:0xc8a96e,roughness:0.5,metalness:0.3});
  const pMat = new THREE.MeshStandardMaterial({color:0xa0724a,roughness:0.6,metalness:0.1,transparent:true,opacity:0.88});
  const vMat = new THREE.MeshStandardMaterial({color:0x050810,roughness:1,transparent:true,opacity:0.9});
  const t = 0.6, dD = 3;
  const void_ = new THREE.Mesh(new THREE.BoxGeometry(dW, dH, dD+2), vMat);
  void_.position.set(0, Y+dH/2, 0); grp.add(void_);
  [[t,dH+t,dD,-dW/2-t/2],[t,dH+t,dD,dW/2+t/2]].forEach(([w,h,d,x])=>{
    const b=new THREE.Mesh(new THREE.BoxGeometry(w,h,d),fMat.clone()); b.position.set(x,Y+dH/2,0); grp.add(b);
  });
  const tf=new THREE.Mesh(new THREE.BoxGeometry(dW+t*2,t,dD),fMat.clone()); tf.position.set(0,Y+dH+t/2,0); grp.add(tf);
  const panel=new THREE.Mesh(new THREE.BoxGeometry(dW-2,dH-2,t),pMat);
  panel.rotation.y=0.35; panel.position.set(-(dW/2-1)*Math.cos(0.35),Y+dH/2,(dW/2-1)*Math.sin(0.35)); grp.add(panel);
  const knob=new THREE.Mesh(new THREE.SphereGeometry(1.8,8,8),new THREE.MeshStandardMaterial({color:0xffd700,metalness:0.9,roughness:0.1}));
  knob.position.set(dW/2-4,Y+dH*0.45,2); grp.add(knob);
  grp.position.set(px,0,pz); grp.rotation.y=rotY;
  scene.add(grp);
  return grp;
}

function addDoor() {
  if (!selEntry) { toast('⚠ Select a room first'); return; }
  const offset = parseInt(document.getElementById('dpOffsetN').value)||50;
  const width  = parseInt(document.getElementById('dpWidthN').value)||28;
  const height = parseInt(document.getElementById('dpHeightN').value)||75;
  const door = { side:curDoorSide, offset:Math.max(5,Math.min(95,offset)), width:Math.max(10,Math.min(80,width)), height:Math.max(20,Math.min(95,height)) };
  if (!selEntry.room.doors) selEntry.room.doors = [];
  selEntry.room.doors.push(door);
  const di = selEntry.room.doors.length-1;
  const grp = buildDoorMesh(selEntry.room, door, selEntry.fi);
  if (grp) doorMeshMap.set(`${selEntry.fi}-${selEntry.ri}-${di}`, grp);
  refreshDoorPanel(); toast('🚪 Door added');
}

function deleteDoor(di) {
  if (!selEntry) return;
  const key = `${selEntry.fi}-${selEntry.ri}-${di}`;
  const g = doorMeshMap.get(key); if(g) scene.remove(g); doorMeshMap.delete(key);
  const n = selEntry.room.doors.length;
  for (let i=di+1;i<n;i++){
    const ok=`${selEntry.fi}-${selEntry.ri}-${i}`, nk=`${selEntry.fi}-${selEntry.ri}-${i-1}`;
    const gg=doorMeshMap.get(ok); if(gg){doorMeshMap.delete(ok);doorMeshMap.set(nk,gg);}
  }
  selEntry.room.doors.splice(di,1);
  refreshDoorPanel(); toast('🗑 Door removed');
}

function rebuildDoorMeshes(entry) {
  if (!entry.room.doors) return;
  for (const [k,g] of doorMeshMap) { if(k.startsWith(`${entry.fi}-${entry.ri}-`)){scene.remove(g);doorMeshMap.delete(k);} }
  entry.room.doors.forEach((d,di)=>{ const g=buildDoorMesh(entry.room,d,entry.fi); if(g) doorMeshMap.set(`${entry.fi}-${entry.ri}-${di}`,g); });
}

// ═══════════════════════════════
// WINDOW EDITOR
// ═══════════════════════════════
let winPanelOpen = false;
let curWinSide = 'front';
let selWinIdx = null;
const winMeshMap = new Map();

function toggleWinPanel() {
  if (!selEntry) { toast('⚠ Select a room first'); return; }
  winPanelOpen = !winPanelOpen;
  if (winPanelOpen && doorPanelOpen) { doorPanelOpen=false; document.getElementById('doorPanel').classList.remove('show'); }
  document.getElementById('winPanel').classList.toggle('show', winPanelOpen);
  document.getElementById('btnWindows').className = 'tb-btn' + (winPanelOpen ? ' active-cyan' : '');
  if (winPanelOpen) { selWinIdx=null; refreshWinPanel(); }
}

function selWinSide(side) {
  curWinSide = side;
  document.querySelectorAll('#wpSides .side-btn').forEach(b => b.classList.toggle('sel-cyan', b.dataset.side === side));
}

function editWinSide(side) {
  document.querySelectorAll('#wpEditSides .side-btn').forEach(b => b.classList.toggle('sel-cyan', b.dataset.side === side));
  if (selWinIdx!==null && selEntry) { selEntry.room.windows[selWinIdx].side=side; rebuildSingleWin(selWinIdx); }
}

function refreshWinPanel() {
  if (!selEntry) return;
  document.getElementById('wpRoomName').textContent = selEntry.room.name;
  const wins = selEntry.room.windows || [];
  const list = document.getElementById('wpList');
  if (!wins.length) { list.innerHTML='<div class="dp-empty">No windows yet</div>'; }
  else {
    list.innerHTML = wins.map((w,i)=>`
      <div class="dp-list-item${selWinIdx===i?' sel-item':''}" onclick="selectWin(${i})">
        <span>🪟</span><span style="color:var(--text)">${w.side}</span>
        <span>pos ${w.offset}% · w${w.width} · h${w.heightPct}%</span>
        <button class="dp-del" onclick="event.stopPropagation();deleteWin(${i})">✕</button>
      </div>`).join('');
  }
  const eb = document.getElementById('wpEditBox');
  if (selWinIdx!==null && wins[selWinIdx]) {
    const w=wins[selWinIdx];
    eb.style.display='block';
    document.getElementById('wpEditIdx').textContent=selWinIdx+1;
    document.getElementById('wpEOffset').value=w.offset; document.getElementById('wpEOffsetN').value=w.offset;
    document.getElementById('wpEWidth').value=w.width;  document.getElementById('wpEWidthN').value=w.width;
    document.getElementById('wpEHeight').value=w.heightPct; document.getElementById('wpEHeightN').value=w.heightPct;
    document.querySelectorAll('#wpEditSides .side-btn').forEach(b=>b.classList.toggle('sel-cyan',b.dataset.side===w.side));
  } else { eb.style.display='none'; }
}

function selectWin(i) { selWinIdx=i; refreshWinPanel(); highlightWin(i); }

function highlightWin(i) {
  if (!selEntry) return;
  (selEntry.room.windows||[]).forEach((_,wi)=>{
    const g=winMeshMap.get(`${selEntry.fi}-${selEntry.ri}-${wi}`);
    if(!g)return;
    g.children.forEach(c=>{if(c.isMesh&&c.material?.emissive){c.material.emissive.setHex(wi===i?0x00e5ff:0);c.material.emissiveIntensity=wi===i?.5:0;}});
  });
}

function liveWin() {
  if (selWinIdx===null||!selEntry) return;
  const w=selEntry.room.windows[selWinIdx];
  w.offset=parseInt(document.getElementById('wpEOffset').value);
  w.width=parseInt(document.getElementById('wpEWidth').value);
  w.heightPct=parseInt(document.getElementById('wpEHeight').value);
  rebuildSingleWin(selWinIdx); refreshWinPanel();
}

function buildWinMesh(room, win, fi) {
  const fg=floorGrps.find(g=>g.fi===fi); if(!fg) return null;
  const Y=fg.Y+SLAB, rH=room.h||60;
  const wW=win.width, wH=rH*(win.heightPct/100), wBot=rH*.25, wCY=Y+wBot+wH/2;
  const grp=new THREE.Group();
  const off=win.offset/100; let px=0,pz=0,rotY=0;
  switch(win.side){
    case 'front':px=room.x+room.w*off;pz=room.z;rotY=0;break;
    case 'back': px=room.x+room.w*off;pz=room.z+room.d;rotY=Math.PI;break;
    case 'left': px=room.x;pz=room.z+room.d*off;rotY=-Math.PI/2;break;
    case 'right':px=room.x+room.w;pz=room.z+room.d*off;rotY=Math.PI/2;break;
  }
  grp.position.set(px,0,pz); grp.rotation.y=rotY;
  const rM=new THREE.Mesh(new THREE.BoxGeometry(wW,wH,10),new THREE.MeshStandardMaterial({color:0x050810,roughness:1}));
  rM.position.set(0,wCY,0); grp.add(rM);
  const gM=new THREE.Mesh(new THREE.BoxGeometry(wW-4,wH-4,1.5),new THREE.MeshStandardMaterial({color:0x88ccee,metalness:0.9,roughness:0.05,transparent:true,opacity:0.4,emissive:0x1a3a55,emissiveIntensity:0.3}));
  gM.position.set(0,wCY,0); grp.add(gM);
  const ft=2, fMat=new THREE.MeshStandardMaterial({color:0xd0d8e8,metalness:0.6,roughness:0.3});
  [[wW,ft,ft,0,wCY+wH/2-ft/2,0],[wW,ft,ft,0,wCY-wH/2+ft/2,0],[ft,wH-ft*2,ft,-wW/2+ft/2,wCY,0],[ft,wH-ft*2,ft,wW/2-ft/2,wCY,0]].forEach(([w,h,d,x,y,z])=>{
    const b=new THREE.Mesh(new THREE.BoxGeometry(w,h,d),fMat.clone()); b.position.set(x,y,z); grp.add(b);
  });
  const midR=new THREE.Mesh(new THREE.BoxGeometry(wW-ft*2,ft,ft),fMat.clone()); midR.position.set(0,wCY,0); grp.add(midR);
  const stile=new THREE.Mesh(new THREE.BoxGeometry(ft,wH-ft*2,ft),fMat.clone()); stile.position.set(0,wCY,0); grp.add(stile);
  const sill=new THREE.Mesh(new THREE.BoxGeometry(wW+8,3,10),new THREE.MeshStandardMaterial({color:0xaabbcc,metalness:0.3,roughness:0.6}));
  sill.position.set(0,wCY-wH/2-1.5,6); grp.add(sill);
  scene.add(grp);
  return grp;
}

function rebuildSingleWin(wi) {
  if(!selEntry) return;
  const key=`${selEntry.fi}-${selEntry.ri}-${wi}`;
  const old=winMeshMap.get(key); if(old) scene.remove(old); winMeshMap.delete(key);
  const win=selEntry.room.windows[wi]; if(!win) return;
  const g=buildWinMesh(selEntry.room,win,selEntry.fi); if(g){winMeshMap.set(key,g);highlightWin(wi);}
}

function addWin() {
  if(!selEntry){toast('⚠ Select a room first');return;}
  const offset=parseInt(document.getElementById('wpOffsetN').value)||50;
  const width=parseInt(document.getElementById('wpWidthN').value)||40;
  const heightPct=parseInt(document.getElementById('wpHeightN').value)||35;
  const win={side:curWinSide,offset:Math.max(5,Math.min(95,offset)),width:Math.max(10,Math.min(120,width)),heightPct:Math.max(10,Math.min(80,heightPct))};
  if(!selEntry.room.windows) selEntry.room.windows=[];
  selEntry.room.windows.push(win);
  const wi=selEntry.room.windows.length-1;
  const g=buildWinMesh(selEntry.room,win,selEntry.fi);
  if(g){winMeshMap.set(`${selEntry.fi}-${selEntry.ri}-${wi}`,g);}
  selWinIdx=wi; refreshWinPanel(); highlightWin(wi); toast('🪟 Window added');
}

function deleteWin(wi) {
  if(!selEntry||!selEntry.room.windows) return;
  const fi=selEntry.fi,ri=selEntry.ri;
  const key=`${fi}-${ri}-${wi}`; const g=winMeshMap.get(key); if(g) scene.remove(g); winMeshMap.delete(key);
  const n=selEntry.room.windows.length;
  for(let i=wi+1;i<n;i++){const ok=`${fi}-${ri}-${i}`,nk=`${fi}-${ri}-${i-1}`;const gg=winMeshMap.get(ok);if(gg){winMeshMap.delete(ok);winMeshMap.set(nk,gg);}}
  selEntry.room.windows.splice(wi,1);
  if(selWinIdx===wi) selWinIdx=null; else if(selWinIdx>wi) selWinIdx--;
  refreshWinPanel(); toast('🗑 Window removed');
}

function deleteSelWin(){if(selWinIdx!==null) deleteWin(selWinIdx);}

function rebuildWinMeshes(entry) {
  if(!entry.room.windows) return;
  const fi=entry.fi,ri=entry.ri;
  for(const[k,g] of winMeshMap){if(k.startsWith(`${fi}-${ri}-`)){scene.remove(g);winMeshMap.delete(k);}}
  entry.room.windows.forEach((w,wi)=>{const g=buildWinMesh(entry.room,w,fi);if(g) winMeshMap.set(`${fi}-${ri}-${wi}`,g);});
}

// ═══════════════════════════════
// SLAB TOOL
// ═══════════════════════════════
let slabMode = false;
let slabs = []; // {fi, id, room:{name,x,z,w,d,t}, cx,cz, mesh,corrMesh,edgMesh}
let selSlab = null;
let slabDragging = false;
let _slabId = 0;

function toggleSlabMode() {
  slabMode = !slabMode;
  document.getElementById('btnSlab').className = 'tb-btn' + (slabMode ? ' active-violet' : '');
  document.getElementById('slabToolbar').classList.toggle('show', slabMode);
  if (!slabMode) { deselectSlab(); closeSlabResizePanel(); }
  toast(slabMode ? '🔲 Slab mode ON — click slab to select' : 'Slab mode OFF');
}

function openAddSlabModal() {
  const box = document.getElementById('modalBox');
  const flOpts = FLOORS.map((f,i)=>`<option value="${i}"${i===activeFloor?' selected':''}>${f.label}</option>`).join('');
  box.innerHTML = `
    <button class="modal-close" onclick="closeModal()">✕</button>
    <div class="modal-title" style="color:#a78bfa">Add Floor Slab</div>
    <div class="m-row"><label class="m-label">Name</label><input class="m-input" id="slabName" value="Slab 1"></div>
    <div class="m-row"><label class="m-label">Floor</label><select class="m-input" id="slabFloor">${flOpts}</select></div>
    <div class="m-grid">
      <div class="m-row"><label class="m-label">Width</label><input type="number" class="m-input" id="slabW" value="${BW}" min="20"></div>
      <div class="m-row"><label class="m-label">Depth</label><input type="number" class="m-input" id="slabD" value="${BD}" min="20"></div>
      <div class="m-row"><label class="m-label">Thickness</label><input type="number" class="m-input" id="slabT" value="7" min="2" max="30"></div>
    </div>
    <div class="m-presets">
      <button class="m-preset" onclick="document.getElementById('slabW').value=${BW};document.getElementById('slabD').value=${BD}">Full (${BW}×${BD})</button>
      <button class="m-preset" onclick="document.getElementById('slabW').value=${BW/2};document.getElementById('slabD').value=${BD}">Half W</button>
      <button class="m-preset" onclick="document.getElementById('slabW').value=${BW};document.getElementById('slabD').value=${BD/2}">Half D</button>
      <button class="m-preset" onclick="document.getElementById('slabW').value=400;document.getElementById('slabD').value=300">400×300</button>
    </div>
    <button class="m-btn" style="background:linear-gradient(135deg,#a78bfa,#6d28d9);color:#fff" onclick="confirmAddSlab()">＋ Add Slab</button>`;
  document.getElementById('modalOverlay').classList.add('show');
}

function confirmAddSlab() {
  const fi=parseInt(document.getElementById('slabFloor').value);
  const name=document.getElementById('slabName').value.trim()||'Slab';
  const w=Math.max(20,parseInt(document.getElementById('slabW').value)||BW);
  const d=Math.max(20,parseInt(document.getElementById('slabD').value)||BD);
  const t=Math.max(2,Math.min(30,parseInt(document.getElementById('slabT').value)||7));
  const cx=BW/2, cz=BD/2;
  _addSlab(fi,{name,x:cx-w/2,z:cz-d/2,w,d,t},cx,cz);
  closeModal(); toast('🔲 '+name+' added'); autoSave();
}

function _addSlab(fi, room, cx, cz) {
  const Y=fi*FH;
  const sMat=new THREE.MeshStandardMaterial({color:0x1a2235,roughness:0.7,metalness:0.15});
  const mesh=new THREE.Mesh(new THREE.BoxGeometry(room.w,room.t,room.d),sMat);
  mesh.position.set(cx,Y+room.t/2,cz); mesh.castShadow=true; mesh.receiveShadow=true; mesh.userData={isSlab:true}; scene.add(mesh);
  const corrMat=new THREE.MeshStandardMaterial({color:0x0f1620,roughness:0.9});
  const corrMesh=new THREE.Mesh(new THREE.BoxGeometry(room.w,1.5,room.d),corrMat);
  corrMesh.position.set(cx,Y+room.t+0.75,cz); corrMesh.receiveShadow=true; corrMesh.userData={isSlab:true}; scene.add(corrMesh);
  const edgMesh=new THREE.LineSegments(new THREE.EdgesGeometry(new THREE.BoxGeometry(room.w+2,room.t+1,room.d+2)),new THREE.LineBasicMaterial({color:0x2a3a5a,transparent:true,opacity:0.5}));
  edgMesh.position.copy(mesh.position); edgMesh.userData={isSlab:true}; scene.add(edgMesh);
  const id=++_slabId;
  const obj={fi,id,room,cx,cz,mesh,corrMesh,edgMesh};
  slabs.push(obj); return obj;
}

function deselectSlab(){if(selSlab)_slabHL(selSlab,false);selSlab=null;document.getElementById('slabSelName').textContent='No slab selected';}
function selectSlab(obj){deselectSlab();selSlab=obj;_slabHL(obj,true);document.getElementById('slabSelName').textContent=obj.room.name;}
function _slabHL(obj,on){[obj.mesh,obj.corrMesh].forEach(m=>{if(m&&m.material){m.material.emissive=m.material.emissive||new THREE.Color(0);m.material.emissive.setHex(on?0xa78bfa:0);m.material.emissiveIntensity=on?.2:0;}});}

function tryPickSlab(e){
  getMouse(e); raycaster.setFromCamera(mouse,camera);
  const meshes=slabs.filter(s=>s.fi===activeFloor).map(s=>s.mesh);
  const hits=raycaster.intersectObjects(meshes);
  if(hits.length){const obj=slabs.find(s=>s.mesh===hits[0].object);if(obj){selectSlab(obj);return;}}
  deselectSlab();
}

function deleteSelSlab(){
  if(!selSlab) return;
  [selSlab.mesh,selSlab.corrMesh,selSlab.edgMesh].forEach(m=>{if(m)scene.remove(m);});
  slabs.splice(slabs.indexOf(selSlab),1);
  selSlab=null; document.getElementById('slabSelName').textContent='No slab selected';
  closeSlabResizePanel(); autoSave(); toast('Slab deleted');
}

function openSlabResizePanel(){
  if(!selSlab) return;
  const r=selSlab.room;
  document.getElementById('slabResName').textContent=r.name;
  document.getElementById('slabRW').value=r.w; document.getElementById('slabRWn').value=r.w;
  document.getElementById('slabRD').value=r.d; document.getElementById('slabRDn').value=r.d;
  document.getElementById('slabRT').value=r.t; document.getElementById('slabRTn').value=r.t;
  document.getElementById('slabPanel').classList.add('show');
}
function closeSlabResizePanel(){document.getElementById('slabPanel').classList.remove('show');}
function slabPreset(w,d){document.getElementById('slabRW').value=w;document.getElementById('slabRWn').value=w;document.getElementById('slabRD').value=d;document.getElementById('slabRDn').value=d;}
function applySlabResize(){
  if(!selSlab) return;
  const w=Math.max(20,parseInt(document.getElementById('slabRWn').value)||selSlab.room.w);
  const d=Math.max(20,parseInt(document.getElementById('slabRDn').value)||selSlab.room.d);
  const t=Math.max(2,Math.min(30,parseInt(document.getElementById('slabRTn').value)||selSlab.room.t));
  const fi=selSlab.fi, cx=selSlab.cx, cz=selSlab.cz;
  [selSlab.mesh,selSlab.corrMesh,selSlab.edgMesh].forEach(m=>{if(m)scene.remove(m);});
  slabs.splice(slabs.indexOf(selSlab),1); selSlab=null;
  const room={name:document.getElementById('slabResName').textContent,x:cx-w/2,z:cz-d/2,w,d,t};
  const obj=_addSlab(fi,room,cx,cz); selectSlab(obj); autoSave(); toast('Slab resized');
  document.getElementById('slabPanel').classList.remove('show');
}

// Hook slab into canvas events
c3d.addEventListener('mousedown', e=>{
  if(slabMode && e.button===0 && currentView!=='grid') tryPickSlab(e);
}, true);

// ═══════════════════════════════
// FLOOR POSITION
// ═══════════════════════════════
let floorPosIdx = null;

function openFloorPos(fi) {
  floorPosIdx = fi;
  const fl = FLOORS[fi];
  document.getElementById('fpFloorName').textContent = fl.label;
  document.getElementById('fpName').value  = fl.label;
  document.getElementById('fpShort').value = fl.short;
  const ox = fl.ox||0, oy = fl.oy||0, oz = fl.oz||0;
  document.getElementById('fpX').value  = ox; document.getElementById('fpXn').value = ox;
  document.getElementById('fpY').value  = oy; document.getElementById('fpYn').value = oy;
  document.getElementById('fpZ').value  = oz; document.getElementById('fpZn').value = oz;
  document.getElementById('floorPosPanel').classList.add('show');
}

function closeFloorPos() {
  document.getElementById('floorPosPanel').classList.remove('show');
  floorPosIdx = null;
}

function liveFloorPos() {
  if (floorPosIdx === null) return;
  const ox = parseFloat(document.getElementById('fpXn').value)||0;
  const oy = parseFloat(document.getElementById('fpYn').value)||0;
  const oz = parseFloat(document.getElementById('fpZn').value)||0;
  const fl = FLOORS[floorPosIdx];
  fl.ox = ox; fl.oy = oy; fl.oz = oz;
  const fg = floorGrps.find(g => g.fi === floorPosIdx);
  if (fg) fg.grp.position.set(ox, oy, oz);
  refreshFloorUI();
  autoSave();
}

function liveFloorName() {
  if (floorPosIdx === null) return;
  const name  = document.getElementById('fpName').value.trim();
  const short = document.getElementById('fpShort').value.trim().toUpperCase();
  const fl = FLOORS[floorPosIdx];
  if (name)  fl.label = name;
  if (short) fl.short = short;
  // Update 3D badge sprite
  const fg = floorGrps.find(g => g.fi === floorPosIdx);
  if (fg) {
    const sp = fg.grp.children.find(c => c.isSprite);
    if (sp) sp.material.map = makeTex(fl.short,'rgba(232,255,60,1)',36);
  }
  document.getElementById('fpFloorName').textContent = fl.label;
  refreshFloorUI(); autoSave();
}

function liveFloorShort() { liveFloorName(); }

function resetFloorPos() {
  if (floorPosIdx === null) return;
  document.getElementById('fpX').value = 0; document.getElementById('fpXn').value = 0;
  document.getElementById('fpY').value = 0; document.getElementById('fpYn').value = 0;
  document.getElementById('fpZ').value = 0; document.getElementById('fpZn').value = 0;
  liveFloorPos();
}

function closeModal(){document.getElementById('modalOverlay').classList.remove('show');}
document.getElementById('modalOverlay').addEventListener('mousedown',e=>{if(e.target===document.getElementById('modalOverlay'))closeModal();});

// ═══════════════════════════════
// BUILDING DIMENSIONS
// ═══════════════════════════════

function applyBuildingDims(rebuild) {
  const nw = Math.max(100, parseInt(document.getElementById('bwInput').value)||BW);
  const nd = Math.max(100, parseInt(document.getElementById('bdInput').value)||BD);
  const nh = Math.max(40,  parseInt(document.getElementById('fhInput').value)||FH);
  BW = nw; BD = nd; FH = nh;
  if (rebuild) {
    // Remove all door/window meshes
    for (const [k,g] of doorMeshMap) { disposeGroup(g); doorMeshMap.delete(k); }
    for (const [k,g] of winMeshMap)  { disposeGroup(g); winMeshMap.delete(k); }
    // Remove all room meshes
    roomMeshes.slice().forEach(e=>{
      [e.mesh,e.edgeMesh].forEach(o=>{if(o){if(o.geometry)o.geometry.dispose();if(o.material)o.material.dispose();scene.remove(o);}});
      if(e.roofMesh){if(e.roofMesh._isLRoof){e.roofMesh.children.forEach(c=>{if(c.geometry)c.geometry.dispose();if(c.material)c.material.dispose();});scene.remove(e.roofMesh);}else{if(e.roofMesh.geometry)e.roofMesh.geometry.dispose();if(e.roofMesh.material)e.roofMesh.material.dispose();scene.remove(e.roofMesh);}}
      if(e.labelSprite){e.labelSprite.material.map?.dispose();e.labelSprite.material.dispose();scene.remove(e.labelSprite);}
    });
    roomMeshes.length = 0;
    // Remove all floor groups
    floorGrps.slice().forEach(fg => disposeGroup(fg.grp));
    floorGrps.length = 0;
    // Rebuild floors and rooms
    FLOORS.forEach((_,fi) => {
      const fl = FLOORS[fi];
      const Y = fi * FH;
      const grp = new THREE.Group();
      const wMat = new THREE.MeshStandardMaterial({color:0x1c2438,roughness:0.55,metalness:0.2});
      const wallH = FH - SLAB, wallY = Y + SLAB + wallH/2;
      const slabM = new THREE.Mesh(new THREE.BoxGeometry(BW+WT*2,SLAB,BD+WT*2),new THREE.MeshStandardMaterial({color:0x141824,roughness:0.7}));
      slabM.position.set(BW/2,Y+SLAB/2,BD/2); slabM.castShadow=true; slabM.receiveShadow=true; grp.add(slabM);
      [[BW+WT*2,wallH,WT,BW/2,wallY,-WT/2],[BW+WT*2,wallH,WT,BW/2,wallY,BD+WT/2],[WT,wallH,BD,-WT/2,wallY,BD/2],[WT,wallH,BD,BW+WT/2,wallY,BD/2]].forEach(([w,h,d,x,y,z])=>{
        const m=new THREE.Mesh(new THREE.BoxGeometry(w,h,d),wMat.clone()); m.position.set(x,y,z); m.castShadow=true; grp.add(m);
      });
      const bSp=new THREE.Sprite(new THREE.SpriteMaterial({map:makeTex(fl.short,'rgba(232,255,60,1)',36),depthTest:false}));
      bSp.scale.set(50,12,1); bSp.position.set(-WT-22,Y+SLAB+wallH/2,BD/2); grp.add(bSp);
      scene.add(grp); grp.position.set(fl.ox||0, fl.oy||0, fl.oz||0); floorGrps.push({grp,fi,Y,floor:fl});
      fl.rooms.forEach((r,ri)=>{
        const e = buildRoomMeshes(r, fi, ri);
        if (e) { rebuildDoorMeshes({fi,ri,room:r}); rebuildWinMeshes({fi,ri,room:r}); }
      });
    });
    // Rebuild staircases
    for (const [id,g] of stairMeshGrps) { disposeGroup(g); stairMeshGrps.delete(id); }
    staircases.forEach(sc => { const g=buildStairMesh(sc); if(g) stairMeshGrps.set(sc.id,g); });
    // Update stair slider maxes
    document.getElementById('stairX').max = nw - 20;
    document.getElementById('stairZ').max = nd - 20;
    activateFloor(activeFloor);
    tgt.set(nw/2, FLOORS.length*nh/2||0, nd/2); camUp();
    toast('↺ Building rebuilt: ' + nw + '×' + nd + ' h=' + nh);
  } else {
    toast('Dims set — click Apply & Rebuild to update 3D');
  }
}

// ═══════════════════════════════
// STAIRCASE INTERACTION
// ═══════════════════════════════
let selStair = null;
let stairDragging = false;
let stairDragOffset = new THREE.Vector3();
let stairDragPlane = new THREE.Plane(new THREE.Vector3(0,1,0), 0);
let stairDragIntersect = new THREE.Vector3();

function _stairAllMeshes() {
  const meshes = [];
  for (const [id, grp] of stairMeshGrps) {
    grp.traverse(c => { if(c.isMesh) meshes.push({mesh:c, id}); });
  }
  return meshes;
}

function tryPickStair(e) {
  if(!moveTool && !selectTool) return false;
  getMouse(e);
  raycaster.setFromCamera(mouse, camera);

  // Collect all stair groups for recursive intersection
  const allGrps = [];
  for (const [id, grp] of stairMeshGrps) allGrps.push(grp);
  if (!allGrps.length) return false;

  // Use recursive=true so nested meshes inside groups are hit-tested
  const hits = raycaster.intersectObjects(allGrps, true);
  if (!hits.length) return false;

  // Walk up from the hit object to find which stair group it belongs to
  let obj = hits[0].object;
  let stairId = null;
  while (obj) {
    if (obj.userData.stairId !== undefined) { stairId = obj.userData.stairId; break; }
    obj = obj.parent;
  }
  if (stairId === null) return false;
  const sc = staircases.find(s => s.id === stairId);
  if (!sc) return false;

  // Deselect previous
  if (selStair && selStair.id !== sc.id) {
    _highlightStair(selStair, false);
  }
  selStair = sc;
  _highlightStair(sc, true);

  // Show stair toolbar
  const tb = document.getElementById('stairToolbar');
  tb.style.display = 'flex';
  document.getElementById('stairTbLabel').textContent = '🪜 ' + sc.name;
  const deg = ((Math.round(THREE.MathUtils.radToDeg(sc.rotY || 0)) % 360) + 360) % 360;
  document.getElementById('stairTbRot').textContent = deg + '°';

  // Auto-open edit panel in select mode
  if (selectTool && !moveTool) {
    openStairEditPanel();
  }

  // Update stair panel if open
  if (document.getElementById('stairsPanel').classList.contains('show')) refreshStairList();

  // Start drag if in move mode
  if (moveTool) {
    stairDragPlane.constant = -(sc.fi * FH);
    raycaster.ray.intersectPlane(stairDragPlane, stairDragIntersect);
    const grp = stairMeshGrps.get(sc.id);
    if (grp) {
      stairDragOffset.set(
        stairDragIntersect.x - grp.position.x,
        0,
        stairDragIntersect.z - grp.position.z
      );
    }
    stairDragging = true;
  }
  return true;
}

function doStairDrag(e) {
  if (!stairDragging || !selStair) return;
  getMouse(e);
  raycaster.setFromCamera(mouse, camera);
  if (!raycaster.ray.intersectPlane(stairDragPlane, stairDragIntersect)) return;
  const grp = stairMeshGrps.get(selStair.id);
  if (!grp) return;
  const nx = stairDragIntersect.x - stairDragOffset.x;
  const nz = stairDragIntersect.z - stairDragOffset.z;
  grp.position.set(nx, grp.position.y, nz);
  selStair.x = nx;
  selStair.z = nz;
}

function endStairDrag() {
  if (stairDragging) { stairDragging = false; dragging = false; autoSave(); if(currentView==='grid') drawGridCanvas(); }
}

function _highlightStair(sc, on) {
  const grp = stairMeshGrps.get(sc.id);
  if (!grp) return;
  grp.traverse(c => {
    if (c.isMesh && c.material) {
      c.material.emissive = c.material.emissive || new THREE.Color(0);
      c.material.emissive.setHex(on ? 0xeab308 : 0);
      c.material.emissiveIntensity = on ? 0.3 : 0;
    }
  });
}

function rotateStair(deg) {
  if (!selStair) return;
  selStair.rotY = (selStair.rotY || 0) + THREE.MathUtils.degToRad(deg);
  const grp = stairMeshGrps.get(selStair.id);
  if (grp) grp.rotation.y = selStair.rotY;
  document.getElementById('stairTbRot').textContent = Math.round(THREE.MathUtils.radToDeg(selStair.rotY) % 360 + 360) % 360 + '°';
  autoSave(); if(currentView==='grid') drawGridCanvas();
}

// Delete selected stair with Del key (hooked into existing keydown)
function deleteSelStair() {
  if (!selStair) return;
  deleteStair(selStair.id);
  selStair = null;
  document.getElementById('stairToolbar').style.display = 'none';
}

// ═══════════════════════════════
// STAIRCASE TOOL
// ═══════════════════════════════
let staircases = [];
let _stairId = 0;
let curStairStyle = 'straight';
const stairMeshGrps = new Map(); // id -> THREE.Group

function selStairStyle(s) {
  curStairStyle = s;
  ['straight','L','U','curve','spiral'].forEach(k => document.getElementById('sStyle'+k.charAt(0).toUpperCase()+k.slice(1)).classList.toggle('sel', k===s));
}

function openStairsModal() {
  const panel = document.getElementById('stairsPanel');
  panel.classList.toggle('show');
  if (panel.classList.contains('show')) {
    // Populate floor dropdowns
    const from = document.getElementById('stairFrom');
    const to   = document.getElementById('stairTo');
    from.innerHTML = to.innerHTML = FLOORS.map((f,i)=>`<option value="${i}">${f.label}</option>`).join('');
    if (FLOORS.length > 1) to.selectedIndex = Math.min(1, FLOORS.length-1);
    // Update slider maxes based on current BW/BD
    const cBW = parseInt(document.getElementById('bwInput').value)||BW;
    const cBD = parseInt(document.getElementById('bdInput').value)||BD;
    document.getElementById('stairX').max = cBW - 20;
    document.getElementById('stairZ').max = cBD - 20;
    refreshStairList();
  }
}

function refreshStairList() {
  const list = document.getElementById('stairList');
  if (!staircases.length) { list.innerHTML = '<div class="dp-empty">No staircases yet</div>'; return; }
  list.innerHTML = staircases.map(s=>`
    <div class="dp-list-item">
      <span>🪜</span>
      <span style="color:var(--text)">${s.name}</span>
      <span>F${s.fi}→F${s.fiTo} · ${s.style} · W:${s.w} D:${s.d}${s.h ? ' H:'+s.h : ''}${s.l ? ' L:'+s.l : ''}</span>
      <button class="dp-del" onclick="deleteStair(${s.id})">✕</button>
    </div>`).join('');
}

function buildStairMesh(sc) {
  const floorY = sc.fi * FH;
  const totalRise = FH * Math.max(1, sc.fiTo - sc.fi);
  const N = 12; // number of steps
  const grp = new THREE.Group();
  const mat = new THREE.MeshStandardMaterial({color:0xd4c5a9, roughness:0.5, metalness:0.1});
  const strMat = new THREE.MeshStandardMaterial({color:0xb8a070, roughness:0.65});
  const railMat = new THREE.MeshStandardMaterial({color:0x8899aa, metalness:0.6, roughness:0.3});

  // Helper: add a baluster post at a given x, stepIndex, zPos
  function addPost(px, pz, baseY, postH) {
    const post = new THREE.Mesh(new THREE.BoxGeometry(3, postH, 3), railMat.clone());
    post.position.set(px, baseY + postH / 2, pz);
    grp.add(post);
  }

  if (sc.style === 'spiral') {
    // ── SPIRAL ──────────────────────────────────────────────────
    // Each tread is a proper trapezoidal wedge radiating from the centre pole.
    // Inner edge hugs the pole, outer edge is wider — fan-shaped like a real spiral stair.
    const R      = sc.w / 2;
    const poleR  = Math.max(5, R * 0.13);
    const stepH  = totalRise / N;
    const tH     = 5;                          // tread slab thickness
    const postH  = 22;
    const dAngle = (Math.PI * 2.0) / N;       // full 360° spread across N steps
    const totalAng = Math.PI * 2.0;

    for (let i = 0; i < N; i++) {
      const a0 = i * dAngle;                  // leading edge angle
      const a1 = a0 + dAngle * 0.92;          // trailing edge (slight gap between treads)
      const y0 = i * stepH;
      const y1 = y0 + tH;

      // Tread: trapezoidal wedge with 8 vertices (bottom + top trapezoid)
      // Inner corners follow pole radius, outer corners follow R
      const ix0 = Math.cos(a0) * poleR, iz0 = Math.sin(a0) * poleR;
      const ox0 = Math.cos(a0) * R,     oz0 = Math.sin(a0) * R;
      const ix1 = Math.cos(a1) * poleR, iz1 = Math.sin(a1) * poleR;
      const ox1 = Math.cos(a1) * R,     oz1 = Math.sin(a1) * R;

      const verts = new Float32Array([
        // bottom (y0)
        ix0, y0, iz0,   ox0, y0, oz0,   ox1, y0, oz1,   ix1, y0, iz1,
        // top (y1)
        ix0, y1, iz0,   ox0, y1, oz0,   ox1, y1, oz1,   ix1, y1, iz1,
      ]);
      const idx = new Uint16Array([
        0,2,1, 0,3,2,   // bottom
        4,5,6, 4,6,7,   // top
        0,1,5, 0,5,4,   // leading edge
        2,3,7, 2,7,6,   // trailing edge
        1,2,6, 1,6,5,   // outer arc
        3,0,4, 3,4,7,   // inner arc
      ]);
      const geo = new THREE.BufferGeometry();
      geo.setAttribute('position', new THREE.BufferAttribute(verts, 3));
      geo.setIndex(new THREE.BufferAttribute(idx, 1));
      geo.computeVertexNormals();
      grp.add(new THREE.Mesh(geo, mat.clone()));

      // Riser — vertical face at the leading edge of each tread
      const rVerts = new Float32Array([
        ix0, y0 - stepH + tH, iz0,   ox0, y0 - stepH + tH, oz0,
        ox0, y0, oz0,                 ix0, y0, iz0,
      ]);
      const rIdx = new Uint16Array([0,2,1, 0,3,2]);
      const rGeo = new THREE.BufferGeometry();
      rGeo.setAttribute('position', new THREE.BufferAttribute(rVerts, 3));
      rGeo.setIndex(new THREE.BufferAttribute(rIdx, 1));
      rGeo.computeVertexNormals();
      if (i > 0) grp.add(new THREE.Mesh(rGeo, mat.clone()));

      if (i > 0) grp.add(new THREE.Mesh(rGeo, mat.clone()));

      // Baluster at outer mid-angle
      const aMid = (a0 + a1) / 2;
      const bx = Math.cos(aMid) * (R - 3);
      const bz = Math.sin(aMid) * (R - 3);
      const bal = new THREE.Mesh(new THREE.BoxGeometry(2.5, postH, 2.5), railMat.clone());
      bal.position.set(bx, y1 + postH / 2, bz);
      grp.add(bal);
    }

    // Centre pole — removed
    // Helical outer handrail
    const railSegs = N * 6;
    for (let i = 0; i < railSegs; i++) {
      const a0 = (i / railSegs) * totalAng;
      const a1 = ((i + 1) / railSegs) * totalAng;
      const y0 = (i / railSegs) * totalRise + 22;
      const y1 = ((i + 1) / railSegs) * totalRise + 22;
      const x0 = Math.cos(a0) * (R - 3), z0 = Math.sin(a0) * (R - 3);
      const x1 = Math.cos(a1) * (R - 3), z1 = Math.sin(a1) * (R - 3);
      const dx = x1-x0, dy = y1-y0, dz = z1-z0;
      const len = Math.sqrt(dx*dx + dy*dy + dz*dz);
      const seg = new THREE.Mesh(new THREE.CylinderGeometry(1.8, 1.8, len, 5), railMat.clone());
      seg.position.set((x0+x1)/2, (y0+y1)/2, (z0+z1)/2);
      seg.quaternion.setFromUnitVectors(new THREE.Vector3(0,1,0), new THREE.Vector3(dx,dy,dz).normalize());
      grp.add(seg);
    }

  } else if (sc.style === 'L') {
    // ── L-SHAPED ────────────────────────────────────────────────
    const half = Math.floor(N / 2);
    const stepH = totalRise / N;
    const tH = 5; // fixed thin tread slab
    const rH = stepH - tH;
    const run1D = sc.d * 0.46;   // total Z run for first flight
    const run2D = sc.d * 0.46;   // total X run for second flight
    const s1D = run1D / half;
    const s2D = run2D / half;
    const postH = 22;

    // ── Run 1: steps along +Z, width along X centred at 0
    for (let i = 0; i < half; i++) {
      // Tread
      const t = new THREE.Mesh(new THREE.BoxGeometry(sc.w, tH, s1D), mat.clone());
      t.position.set(0, i * stepH + tH / 2, i * s1D + s1D / 2);
      grp.add(t);
      // Riser
      const r = new THREE.Mesh(new THREE.BoxGeometry(sc.w, rH, 2), mat.clone());
      r.position.set(0, i * stepH + tH + rH / 2, i * s1D);
      grp.add(r);
      // Balusters on both sides
      [-sc.w / 2 - 1, sc.w / 2 + 1].forEach(bx => {
        addPost(bx, i * s1D + s1D / 2, i * stepH + tH, postH);
      });
    }

    // Landing platform — connects the two runs
    const landY = half * stepH;
    const landZ = half * s1D; // front edge of landing = end of run 1
    const landX = sc.w / 2 + s2D; // landing extends into run 2 direction
    const landing = new THREE.Mesh(new THREE.BoxGeometry(sc.w + s2D, tH, sc.w), mat.clone());
    landing.position.set(s2D / 2, landY + tH / 2, landZ + sc.w / 2);
    grp.add(landing);

    // ── Run 2: steps along +X from landing, width along Z
    const r2StartX = sc.w / 2 + s2D / 2; // first tread centre X
    const r2Z = landZ + sc.w / 2; // centred on landing Z
    for (let i = 0; i < half; i++) {
      const t = new THREE.Mesh(new THREE.BoxGeometry(s2D, tH, sc.w), mat.clone());
      t.position.set(sc.w / 2 + (i + 1) * s2D - s2D / 2, landY + (i + 1) * stepH + tH / 2, r2Z);
      grp.add(t);
      const r = new THREE.Mesh(new THREE.BoxGeometry(2, rH, sc.w), mat.clone());
      r.position.set(sc.w / 2 + (i + 1) * s2D - s2D, landY + (i + 1) * stepH + tH + rH / 2, r2Z);
      grp.add(r);
      // Balusters on both sides
      [r2Z - sc.w / 2 - 1, r2Z + sc.w / 2 + 1].forEach(bz => {
        addPost(sc.w / 2 + (i + 1) * s2D - s2D / 2, bz, landY + (i + 1) * stepH + tH, postH);
      });
    }

    // Handrail run 1 (along +Z)
    const railAngle1 = Math.atan2(half * stepH, run1D);
    const railLen1 = Math.sqrt(run1D * run1D + (half * stepH) * (half * stepH));
    [-sc.w / 2 - 2, sc.w / 2 + 2].forEach(rx => {
      const rail = new THREE.Mesh(new THREE.BoxGeometry(3, 3, railLen1), railMat.clone());
      rail.rotation.x = -railAngle1;
      rail.position.set(rx, half * stepH / 2 + 22, run1D / 2);
      grp.add(rail);
    });

    // Handrail run 2 (along +X)
    const railAngle2 = Math.atan2(half * stepH, run2D);
    const railLen2 = Math.sqrt(run2D * run2D + (half * stepH) * (half * stepH));
    [r2Z - sc.w / 2 - 2, r2Z + sc.w / 2 + 2].forEach(rz => {
      const rail = new THREE.Mesh(new THREE.BoxGeometry(railLen2, 3, 3), railMat.clone());
      rail.rotation.z = railAngle2;
      rail.position.set(sc.w / 2 + run2D / 2, landY + half * stepH / 2 + 22, rz);
      grp.add(rail);
    });

  } else if (sc.style === 'U') {
    // ── U-SHAPED ────────────────────────────────────────────────
    // Run 1: left flight going +Z
    // Landing: at far end connecting both flights
    // Run 2: right flight coming back -Z (parallel to run 1)
    const third = Math.floor(N / 2);
    const rem   = N - third * 2;
    const stepH = totalRise / N;
    const tH    = 5;
    const rH    = stepH - tH;
    const gap   = 12;
    const flightW = (sc.w - gap) / 2;
    const runD  = sc.d * 0.85;
    const s1D   = runD / third;
    const run2Steps = third + rem;
    const s2D   = runD / run2Steps;
    const postH = 22;
    const run2X = flightW + gap;

    // ── Run 1: left flight, +Z direction
    for (let i = 0; i < third; i++) {
      const t = new THREE.Mesh(new THREE.BoxGeometry(flightW, tH, s1D), mat.clone());
      t.position.set(flightW / 2, i * stepH + tH / 2, i * s1D + s1D / 2);
      grp.add(t);
      const r = new THREE.Mesh(new THREE.BoxGeometry(flightW, rH, 2), mat.clone());
      r.position.set(flightW / 2, i * stepH + tH + rH / 2, i * s1D);
      grp.add(r);
      // Balusters on outer sides
      addPost(-2, i * s1D + s1D / 2, i * stepH + tH, postH);
      addPost(flightW + 2, i * s1D + s1D / 2, i * stepH + tH, postH);
    }

    // ── Landing at top of run 1
    const landY = third * stepH;
    const landing = new THREE.Mesh(new THREE.BoxGeometry(sc.w, tH, flightW), mat.clone());
    landing.position.set(sc.w / 2, landY + tH / 2, runD + flightW / 2);
    grp.add(landing);

    // ── Run 2: right flight, coming back in -Z direction
    for (let i = 0; i < run2Steps; i++) {
      const zPos = runD - i * s2D;
      const t = new THREE.Mesh(new THREE.BoxGeometry(flightW, tH, s2D), mat.clone());
      t.position.set(run2X + flightW / 2, landY + (i + 1) * stepH + tH / 2, zPos - s2D / 2);
      grp.add(t);
      const r = new THREE.Mesh(new THREE.BoxGeometry(flightW, rH, 2), mat.clone());
      r.position.set(run2X + flightW / 2, landY + (i + 1) * stepH + tH + rH / 2, zPos);
      grp.add(r);
      // Balusters on outer sides
      addPost(run2X - 2, zPos - s2D / 2, landY + (i + 1) * stepH + tH, postH);
      addPost(run2X + flightW + 2, zPos - s2D / 2, landY + (i + 1) * stepH + tH, postH);
    }

    // Handrail run 1 (along +Z)
    const ang1 = Math.atan2(third * stepH, runD);
    const len1 = Math.sqrt(runD * runD + (third * stepH) ** 2);
    [-2, flightW + 2].forEach(rx => {
      const rail = new THREE.Mesh(new THREE.BoxGeometry(3, 3, len1), railMat.clone());
      rail.rotation.x = -ang1;
      rail.position.set(rx, third * stepH / 2 + 22, runD / 2);
      grp.add(rail);
    });

    // Handrail run 2 (along -Z)
    const ang2 = Math.atan2(run2Steps * stepH, runD);
    const len2 = Math.sqrt(runD * runD + (run2Steps * stepH) ** 2);
    [run2X - 2, run2X + flightW + 2].forEach(rx => {
      const rail = new THREE.Mesh(new THREE.BoxGeometry(3, 3, len2), railMat.clone());
      rail.rotation.x = ang2;
      rail.position.set(rx, landY + run2Steps * stepH / 2 + 22, runD / 2);
      grp.add(rail);
    });

  } else if (sc.style === 'curve') {
    // ── CURVED ──────────────────────────────────────────────────
    // A quarter-circle arc sweep (90°) with the centre of curvature at (0, 0, R)
    // Treads are trapezoidal slabs that fan outward along the arc.
    // Inner radius = sc.w * 0.35, outer radius = inner + sc.w * 0.65
    const stepH   = totalRise / N;
    const tH      = 5;
    const rH      = stepH - tH;
    const postH   = 22;
    const sweepAng = Math.PI / 2;           // 90° total sweep
    const dAng    = sweepAng / N;
    const Ri      = sc.w * 0.35;            // inner radius
    const Ro      = Ri + sc.w * 0.65;       // outer radius
    const Rm      = (Ri + Ro) / 2;          // mid radius (for post placement)
    const cx      = 0;                       // arc centre X
    const cz      = sc.d * 0.5;             // arc centre Z — pushes stairs forward

    for (let i = 0; i < N; i++) {
      const a0 = -Math.PI / 2 + i * dAng;       // start angle of this tread
      const a1 = a0 + dAng;                       // end angle
      const aMid = (a0 + a1) / 2;

      // Build tread as a trapezoid using a custom BufferGeometry
      // Four corners: inner-start, outer-start, outer-end, inner-end
      const ix0 = cx + Math.cos(a0) * Ri,  iz0 = cz + Math.sin(a0) * Ri;
      const ox0 = cx + Math.cos(a0) * Ro,  oz0 = cz + Math.sin(a0) * Ro;
      const ix1 = cx + Math.cos(a1) * Ri,  iz1 = cz + Math.sin(a1) * Ri;
      const ox1 = cx + Math.cos(a1) * Ro,  oz1 = cz + Math.sin(a1) * Ro;
      const y0  = i * stepH;
      const y1  = y0 + tH;

      // Vertices: bottom face then top face (trapezoid)
      const verts = new Float32Array([
        // bottom face (y0)
        ix0, y0, iz0,   ox0, y0, oz0,   ox1, y0, oz1,   ix1, y0, iz1,
        // top face (y1)
        ix0, y1, iz0,   ox0, y1, oz0,   ox1, y1, oz1,   ix1, y1, iz1,
      ]);
      // Indices for 6 faces (box-like)
      const idx = new Uint16Array([
        0,2,1, 0,3,2,       // bottom
        4,5,6, 4,6,7,       // top
        0,1,5, 0,5,4,       // front (a0 edge)
        2,3,7, 2,7,6,       // back (a1 edge)
        1,2,6, 1,6,5,       // outer arc edge
        3,0,4, 3,4,7,       // inner arc edge
      ]);
      const geo = new THREE.BufferGeometry();
      geo.setAttribute('position', new THREE.BufferAttribute(verts, 3));
      geo.setIndex(new THREE.BufferAttribute(idx, 1));
      geo.computeVertexNormals();
      const tread = new THREE.Mesh(geo, mat.clone());
      grp.add(tread);

      // Riser — thin vertical face at the front (a0 edge) of each tread
      if (i > 0) {
        const rVerts = new Float32Array([
          ix0, y0 - rH, iz0,   ox0, y0 - rH, oz0,
          ox0, y0,      oz0,   ix0, y0,      iz0,
        ]);
        const rIdx = new Uint16Array([0,2,1, 0,3,2]);
        const rGeo = new THREE.BufferGeometry();
        rGeo.setAttribute('position', new THREE.BufferAttribute(rVerts, 3));
        rGeo.setIndex(new THREE.BufferAttribute(rIdx, 1));
        rGeo.computeVertexNormals();
        grp.add(new THREE.Mesh(rGeo, mat.clone()));
      }

      // Outer baluster
      const bx = cx + Math.cos(aMid) * (Ro + 3);
      const bz = cz + Math.sin(aMid) * (Ro + 3);
      addPost(bx, bz, i * stepH + tH, postH);
    }

    // Curved handrails (inner & outer) as segmented arcs
    [[Ri - 4, 'inner'], [Ro + 4, 'outer']].forEach(([rad]) => {
      const segs = N * 4;
      for (let i = 0; i < segs; i++) {
        const a0 = -Math.PI / 2 + (i / segs) * sweepAng;
        const a1 = -Math.PI / 2 + ((i + 1) / segs) * sweepAng;
        const y0 = (i / segs) * totalRise + 22;
        const y1 = ((i + 1) / segs) * totalRise + 22;
        const x0 = cx + Math.cos(a0) * rad, z0 = cz + Math.sin(a0) * rad;
        const x1 = cx + Math.cos(a1) * rad, z1 = cz + Math.sin(a1) * rad;
        const dx = x1 - x0, dy = y1 - y0, dz = z1 - z0;
        const len = Math.sqrt(dx*dx + dy*dy + dz*dz);
        const seg = new THREE.Mesh(new THREE.CylinderGeometry(1.8, 1.8, len, 5), railMat.clone());
        seg.position.set((x0+x1)/2, (y0+y1)/2, (z0+z1)/2);
        seg.quaternion.setFromUnitVectors(new THREE.Vector3(0,1,0), new THREE.Vector3(dx,dy,dz).normalize());
        grp.add(seg);
      }
    });

  } else {
    // ── STRAIGHT ────────────────────────────────────────────────
    const stepH = totalRise / N;
    const stepD = sc.d / N;
    const tH = 5; // fixed thin tread slab
    const rH = stepH - tH;
    const postH = 22;
    const railAngle = Math.atan2(totalRise, sc.d);
    const railLen = Math.sqrt(sc.d * sc.d + totalRise * totalRise);

    for (let i = 0; i < N; i++) {
      // Tread
      const t = new THREE.Mesh(new THREE.BoxGeometry(sc.w, tH, stepD), mat.clone());
      t.position.set(0, i * stepH + tH / 2, i * stepD + stepD / 2);
      grp.add(t);
      // Riser (thin vertical face at back of tread)
      const r = new THREE.Mesh(new THREE.BoxGeometry(sc.w, rH, 2), mat.clone());
      r.position.set(0, i * stepH + tH + rH / 2, i * stepD);
      grp.add(r);
      // Balusters (one on each side)
      [-sc.w / 2 - 1, sc.w / 2 + 1].forEach(bx => {
        addPost(bx, i * stepD + stepD * 0.5, i * stepH + tH, postH);
      });
    }

    // Handrails — two angled rails, one on each side
    [-sc.w / 2 - 2, sc.w / 2 + 2].forEach(rx => {
      const rail = new THREE.Mesh(new THREE.BoxGeometry(4, 4, railLen), railMat.clone());
      rail.rotation.x = -railAngle;
      rail.position.set(rx, totalRise / 2 + 22, sc.d / 2);
      grp.add(rail);
    });
  }

  grp.position.set(sc.x, floorY, sc.z);
  grp.rotation.y = sc.rotY || 0;
  grp.userData.stairId = sc.id;
  scene.add(grp);
  return grp;
}

function addStaircase() {
  if (FLOORS.length < 1) { toast('⚠ Add at least 1 floor first'); return; }
  const fi   = parseInt(document.getElementById('stairFrom').value);
  const fiTo = parseInt(document.getElementById('stairTo').value);
  const name = document.getElementById('stairName').value.trim() || 'Staircase';
  const w    = Math.max(20, parseInt(document.getElementById('stairWn').value)||60);
  const d    = Math.max(30, parseInt(document.getElementById('stairDn').value)||120);
  const h    = Math.max(10, parseInt(document.getElementById('stairHn').value)||90);
  const l    = Math.max(30, parseInt(document.getElementById('stairLn').value)||120);
  const x    = parseInt(document.getElementById('stairXn').value)||100;
  const z    = parseInt(document.getElementById('stairZn').value)||100;
  const id   = ++_stairId;
  const sc   = { id, name, fi, fiTo, w, d, h, l, x, z, style: curStairStyle, rotY: 0 };
  staircases.push(sc);
  const grp = buildStairMesh(sc);
  if (grp) stairMeshGrps.set(id, grp);
  refreshStairList(); autoSave(); if(currentView==='grid') drawGridCanvas(); toast('🪜 '+name+' added');
}

function deleteStair(id) {
  const g = stairMeshGrps.get(id); if(g) scene.remove(g); stairMeshGrps.delete(id);
  staircases = staircases.filter(s => s.id !== id);
  refreshStairList(); autoSave(); if(currentView==='grid') drawGridCanvas(); toast('Staircase removed');
}

// ═══════════════════════════════
// STAIR EDIT PANEL
// ═══════════════════════════════
let _sepStyle = 'straight';

function openStairEditPanel() {
  if (!selStair) return;
  const sc = selStair;
  document.getElementById('sepName').textContent = sc.name;
  document.getElementById('sepNameInput').value = sc.name;
  document.getElementById('sepW').value = sc.w; document.getElementById('sepWn').value = sc.w;
  document.getElementById('sepD').value = sc.d; document.getElementById('sepDn').value = sc.d;
  document.getElementById('sepH').value = sc.h || 90; document.getElementById('sepHn').value = sc.h || 90;
  document.getElementById('sepL').value = sc.l || sc.d; document.getElementById('sepLn').value = sc.l || sc.d;
  _sepStyle = sc.style || 'straight';
  ['straight','L','U','curve','spiral'].forEach(k => {
    document.getElementById('sepStyle'+k.charAt(0).toUpperCase()+k.slice(1)).classList.toggle('sel', k === _sepStyle);
  });
  document.getElementById('stairEditPanel').classList.add('show');
}

function closeStairEditPanel() {
  document.getElementById('stairEditPanel').classList.remove('show');
}

function sepSelStyle(s) {
  _sepStyle = s;
  ['straight','L','U','curve','spiral'].forEach(k => {
    document.getElementById('sepStyle'+k.charAt(0).toUpperCase()+k.slice(1)).classList.toggle('sel', k === s);
  });
}

function applyStairEdit() {
  if (!selStair) return;
  const sc = selStair;
  sc.name  = document.getElementById('sepNameInput').value.trim() || sc.name;
  sc.w     = Math.max(20, parseInt(document.getElementById('sepWn').value) || sc.w);
  sc.d     = Math.max(30, parseInt(document.getElementById('sepDn').value) || sc.d);
  sc.h     = Math.max(10, parseInt(document.getElementById('sepHn').value) || 90);
  sc.l     = Math.max(30, parseInt(document.getElementById('sepLn').value) || sc.d);
  sc.style = _sepStyle;

  // Rebuild mesh
  const old = stairMeshGrps.get(sc.id);
  if (old) { disposeGroup(old); stairMeshGrps.delete(sc.id); }
  const grp = buildStairMesh(sc);
  if (grp) { stairMeshGrps.set(sc.id, grp); _highlightStair(sc, true); }

  // Update toolbar label
  document.getElementById('stairTbLabel').textContent = '🪜 ' + sc.name;
  document.getElementById('sepName').textContent = sc.name;

  refreshStairList(); autoSave(); if(currentView==='grid') drawGridCanvas();
  toast('✓ Staircase updated');
}

// ═══════════════════════════════
// OVERLAP / GLITCH DETECTION
// ═══════════════════════════════

// Overlap meshes: highlights stored here so we can clear them
let overlapHighlights = [];

/**
 * Returns true if two AABB rooms overlap (on the same floor, ignoring height).
 * Rooms: { x, z, w, d } — x,z is the top-left corner, w=width, d=depth.
 * A tiny 1-unit tolerance avoids false positives from rooms sharing a wall edge.
 */
function roomsOverlap(a, b) {
  const MARGIN = 1;
  return (
    a.x + MARGIN < b.x + b.w &&
    a.x + a.w - MARGIN > b.x &&
    a.z + MARGIN < b.z + b.d &&
    a.z + a.d - MARGIN > b.z
  );
}

/**
 * Scans all rooms on a given floor and returns an array of overlap pairs:
 * [{ roomA, roomB, riA, riB }]
 */
function findOverlapsOnFloor(fi) {
  const rooms = FLOORS[fi]?.rooms || [];
  const pairs = [];
  for (let i = 0; i < rooms.length; i++) {
    for (let j = i + 1; j < rooms.length; j++) {
      if (roomsOverlap(rooms[i], rooms[j])) {
        pairs.push({ roomA: rooms[i], roomB: rooms[j], riA: i, riB: j });
      }
    }
  }
  return pairs;
}

/**
 * Scans ALL floors and returns overlap pairs per floor:
 * [{ fi, floorLabel, pairs: [...] }]
 */
function findAllOverlaps() {
  return FLOORS.map((fl, fi) => ({
    fi,
    floorLabel: fl.label,
    pairs: findOverlapsOnFloor(fi)
  })).filter(r => r.pairs.length > 0);
}

/** Remove all red overlap highlight meshes from the scene */
function clearOverlapHighlights() {
  overlapHighlights.forEach(m => {
    if (m.geometry) m.geometry.dispose();
    if (m.material) m.material.dispose();
    scene.remove(m);
  });
  overlapHighlights = [];
}

/** Draw translucent red boxes over overlapping rooms on the active floor */
function drawOverlapHighlights(fi) {
  clearOverlapHighlights();
  const pairs = findOverlapsOnFloor(fi);
  const Y = fi * FH + SLAB;
  const mat = new THREE.MeshBasicMaterial({
    color: 0xff2244, transparent: true, opacity: 0.28, depthWrite: false, side: THREE.DoubleSide
  });
  const edgeMat = new THREE.LineBasicMaterial({ color: 0xff2244, transparent: true, opacity: 0.9 });

  const highlighted = new Set();
  pairs.forEach(({ riA, riB }) => {
    [riA, riB].forEach(ri => {
      if (highlighted.has(ri)) return;
      highlighted.add(ri);
      const r = FLOORS[fi].rooms[ri];
      const rH = r.h || 60;
      const geo = new THREE.BoxGeometry(r.w, rH + 4, r.d);
      const mesh = new THREE.Mesh(geo, mat.clone());
      mesh.position.set(r.x + r.w / 2, Y + rH / 2, r.z + r.d / 2);
      scene.add(mesh);
      overlapHighlights.push(mesh);

      const edge = new THREE.LineSegments(new THREE.EdgesGeometry(geo), edgeMat.clone());
      edge.position.copy(mesh.position);
      scene.add(edge);
      overlapHighlights.push(edge);
    });
  });
}

/** Build the overlap report HTML and inject into the panel */
function renderOverlapPanel() {
  const el = document.getElementById('overlapContent');
  const allOverlaps = findAllOverlaps();

  if (!allOverlaps.length) {
    el.innerHTML = `<div class="ov-ok">✓ No overlaps found across all floors</div>`;
    document.getElementById('btnOverlap').style.color = '';
    document.getElementById('btnOverlap').style.borderColor = '';
    return;
  }

  let html = '';
  allOverlaps.forEach(({ fi, floorLabel, pairs }) => {
    html += `<div style="font-family:'Space Mono',monospace;font-size:7px;letter-spacing:2px;color:var(--muted);text-transform:uppercase;margin:8px 0 4px">${floorLabel}</div>`;
    pairs.forEach(({ roomA, roomB, riA, riB }) => {
      html += `<div class="ov-item" onclick="jumpToOverlap(${fi},${riA},${riB})" title="Click to highlight">
        <div class="ov-dot"></div>
        <div><span style="color:var(--text)">${roomA.name}</span>
        <span style="color:var(--muted)"> ✕ </span>
        <span style="color:var(--text)">${roomB.name}</span></div>
      </div>`;
    });
  });

  el.innerHTML = html;

  // Tint the check-overlaps button red to signal issues
  const btn = document.getElementById('btnOverlap');
  btn.style.color = 'var(--accent2)';
  btn.style.borderColor = 'rgba(255,77,109,.45)';
}

/** Click on an overlap item — activate floor, highlight, and focus */
function jumpToOverlap(fi, riA, riB) {
  activateFloor(fi);
  drawOverlapHighlights(fi);

  // Select first room
  const entry = roomMeshes.find(e => e.fi === fi && e.ri === riA);
  if (entry) {
    deselectRoom(false);
    selectRoom(entry);
  }
  // Pan camera toward overlap zone
  const rA = FLOORS[fi].rooms[riA];
  const rB = FLOORS[fi].rooms[riB];
  const mx = (rA.x + rA.w / 2 + rB.x + rB.w / 2) / 2;
  const mz = (rA.z + rA.d / 2 + rB.z + rB.d / 2) / 2;
  tgt.set(mx, fi * FH, mz);
  sph.r = 600; camUp();
  toast('⚠ Overlapping rooms highlighted in red');
}

let overlapPanelOpen = false;
function toggleOverlapPanel() {
  overlapPanelOpen = !overlapPanelOpen;
  const panel = document.getElementById('overlapPanel');

  if (overlapPanelOpen) {
    renderOverlapPanel();
    drawOverlapHighlights(activeFloor);
    panel.style.display = 'block';
    document.getElementById('btnOverlap').classList.add('active-red');
  } else {
    panel.style.display = 'none';
    clearOverlapHighlights();
    document.getElementById('btnOverlap').classList.remove('active-red');
    // Reset btn tint only if no issues remain
    const btn = document.getElementById('btnOverlap');
    if (!findAllOverlaps().length) {
      btn.style.color = '';
      btn.style.borderColor = '';
    }
  }
}

/** Called automatically after room changes to refresh badge + highlights */
function refreshOverlapState() {
  const issues = findAllOverlaps();
  const btn = document.getElementById('btnOverlap');
  if (issues.length) {
    btn.style.color = 'var(--accent2)';
    btn.style.borderColor = 'rgba(255,77,109,.45)';
  } else {
    btn.style.color = '';
    btn.style.borderColor = '';
  }
  if (overlapPanelOpen) {
    renderOverlapPanel();
    drawOverlapHighlights(activeFloor);
  }
}

</script>

</body>
</html>
