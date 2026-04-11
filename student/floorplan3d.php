<?php
require_once '../php/config.php';
$_sn_conn = getDBConnection();
$_sn_res  = $_sn_conn ? $_sn_conn->query("SELECT setting_key, setting_value FROM system_settings WHERE setting_key IN ('school_name','current_school_year')") : false;
$school_name = 'My School';
$current_school_year = '----';
if ($_sn_res) { while ($_sn_row = $_sn_res->fetch_assoc()) { if ($_sn_row['setting_key']==='school_name') $school_name=$_sn_row['setting_value']; if ($_sn_row['setting_key']==='current_school_year') $current_school_year=$_sn_row['setting_value']; } }
requireRole('student');
$user_name = $_SESSION['name'] ?? 'User';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>3D Campus Map — <?= htmlspecialchars($school_name) ?></title>
<link rel="icon" type="image/jpeg" href="../images/logo2.jpg">
<link href="https://fonts.googleapis.com/css2?family=Space+Mono:wght@400;700&family=Barlow:wght@300;400;500;600;700;900&family=Barlow+Condensed:wght@500;700;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../css/style.css">
<link rel="stylesheet" href="../css/mobile-fix.css">
<link rel="stylesheet" href="../css/themes.css">
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

/* ── Viewer overrides ── */
html, body { height:100%; overflow:hidden; }
#viewer-overlay {
  position:fixed;inset:0;background:#07080c;display:flex;flex-direction:column;
  align-items:center;justify-content:center;z-index:9999;gap:16px;
}
#viewer-overlay.hidden { display:none; }
#overlay-logo {width:40px;height:40px;border-radius:8px;background:#e8ff3c;display:flex;align-items:center;justify-content:center;}
#overlay-msg {font-family:'Barlow Condensed',sans-serif;font-size:13px;letter-spacing:2px;text-transform:uppercase;color:#3a4460;}
#overlay-err {font-size:13px;color:#ff4d6d;text-align:center;max-width:320px;line-height:1.6;display:none;}
#overlay-back {padding:8px 20px;border-radius:4px;background:#e8ff3c;color:#000;font-weight:700;font-size:11px;text-decoration:none;text-transform:uppercase;letter-spacing:.5px;display:none;}
/* viewer topbar */
#topbar { background:#0c0e14;border-bottom:1px solid #1e2535;height:46px;display:flex;align-items:center;z-index:200;position:relative; }
.tb-logo { display:flex;align-items:center;gap:9px;padding:0 16px;height:100%;border-right:1px solid #1e2535;flex-shrink:0; }
.tb-logo-mark {width:24px;height:24px;background:#e8ff3c;border-radius:4px;display:flex;align-items:center;justify-content:center;}
.tb-wordmark {font-family:'Barlow Condensed',sans-serif;font-size:16px;font-weight:900;letter-spacing:2.5px;text-transform:uppercase;color:#edf0fa;}
.tb-wordmark span {color:#e8ff3c;}
.tb-space {flex:1;}
.tb-btn {display:flex;align-items:center;gap:5px;height:30px;padding:0 12px;border-radius:4px;border:1px solid transparent;background:transparent;color:#5a6888;font-family:'Barlow',sans-serif;font-size:10px;font-weight:700;cursor:pointer;transition:all .15s;text-transform:uppercase;letter-spacing:.6px;white-space:nowrap;}
.tb-btn:hover {background:#161921;color:#edf0fa;border-color:#1e2535;}
.tb-btn.active {background:rgba(232,255,60,.1);border-color:rgba(232,255,60,.35);color:#e8ff3c;}
.tb-tools {display:flex;align-items:center;height:100%;padding:0 8px;gap:3px;}
#viewer-meta {display:flex;align-items:center;gap:10px;padding:0 14px;font-family:'Barlow',sans-serif;font-size:11px;color:#5a6888;border-left:1px solid #1e2535;height:100%;}
#viewer-meta strong {color:#edf0fa;}
#viewer-meta a {color:#5a6888;text-decoration:none;padding:3px 8px;border-radius:3px;border:1px solid #1e2535;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;transition:all .15s;}
#viewer-meta a:hover {background:#10121a;color:#edf0fa;}
#layout {display:flex;height:calc(100vh - 46px - 26px);}
#wrap {flex:1;position:relative;overflow:hidden;}
#c3d {width:100%;height:100%;display:block;cursor:grab;}
#c3d:active {cursor:grabbing;}
#statusbar {height:26px;background:#0c0e14;border-top:1px solid #1e2535;display:flex;align-items:center;padding:0 14px;gap:12px;}
.sb-it {display:flex;align-items:center;gap:5px;font-family:'Space Mono',monospace;font-size:8px;color:#3a4460;}
.sb-it kbd {background:#10121a;border:1px solid #26304a;border-radius:2px;padding:0 4px;font-family:'Space Mono',monospace;font-size:8px;}
.sb-dot {width:5px;height:5px;border-radius:50%;background:#00d464;}
#statusMsg {font-family:'Space Mono',monospace;font-size:9px;color:#5a6888;margin-left:auto;}
#layout-info {position:absolute;top:12px;right:12px;background:rgba(8,10,16,.9);border:1px solid #26304a;border-radius:6px;padding:10px 14px;font-family:'Space Mono',monospace;font-size:9px;color:#5a6888;z-index:10;max-width:200px;}
#layout-info strong {display:block;color:#e8ff3c;font-size:10px;margin-bottom:4px;letter-spacing:1px;}
</style>
</head>
<body>

<!-- Loading/error overlay -->
<div id="viewer-overlay">
  <div id="overlay-logo">
    <svg width="22" height="22" viewBox="0 0 22 22" fill="none"><rect x="2" y="2" width="8" height="8" rx="1.5" fill="#000"/><rect x="12" y="2" width="8" height="8" rx="1.5" fill="#000"/><rect x="2" y="12" width="8" height="8" rx="1.5" fill="#000"/><rect x="12" y="12" width="8" height="8" rx="1.5" fill="#000"/></svg>
  </div>
  <div id="overlay-msg">Loading 3D campus map...</div>
  <div id="overlay-err"></div>
  <a href="dashboard.php" id="overlay-back">← Back to Dashboard</a>
</div>

<div id="topbar">
  <div class="tb-logo">
    <div class="tb-logo-mark">
      <svg width="14" height="14" viewBox="0 0 14 14" fill="none"><rect x="1" y="1" width="5" height="5" rx="1" fill="#000"/><rect x="8" y="1" width="5" height="5" rx="1" fill="#000"/><rect x="1" y="8" width="5" height="5" rx="1" fill="#000"/><rect x="8" y="8" width="5" height="5" rx="1" fill="#000"/></svg>
    </div>
    <span class="tb-wordmark">Campus <span>Map 3D</span></span>
  </div>
  <div class="tb-tools">
    <button class="tb-btn active" id="btn3d" onclick="setView('3d')">3D View</button>
    <button class="tb-btn" id="btnFloor" onclick="setView('floor')">Top Down</button>
    <button class="tb-btn" onclick="resetView()">Reset</button>
  </div>
  <div class="tb-space"></div>
  <div id="viewer-meta">
    <span><?= htmlspecialchars($user_name) ?></span>
    <a href="dashboard.php">← Dashboard</a>
  </div>
</div>

<div id="layout">
  <div id="wrap">
    <canvas id="c3d"></canvas>
    <div id="layout-info" style="display:none">
      <strong id="layout-name">—</strong>
      <span id="layout-date"></span>
    </div>
  </div>
</div>

<div id="statusbar">
  <div class="sb-it"><span class="sb-dot"></span><span id="sb-floors">Loading...</span></div>
  <div class="sb-it" style="margin-left:6px"><kbd>drag</kbd> orbit &nbsp;<kbd>scroll</kbd> zoom &nbsp;<kbd>right-drag</kbd> pan</div>
  <span id="statusMsg" style="margin-left:auto"></span>
</div>

<!-- Toast -->
<div id="toast" style="position:fixed;bottom:50px;left:50%;transform:translateX(-50%);background:rgba(8,10,16,.97);border:1px solid #26304a;border-radius:6px;padding:8px 16px;font-family:'Barlow',sans-serif;font-size:12px;color:#edf0fa;display:none;z-index:999;pointer-events:none;white-space:nowrap;"></div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
<script>
// ══ STATE ══
let BW=800, BD=600, FH=90;
const SLAB=6, WT=8;
let FLOORS=[];
let activeFloor=0;
let currentView='3d';
let selEntry=null, hovEntry=null;
let moveTool=false, selectTool=true;
let roomDragging=false;
let drawMode=false;
let toastTimer=null;
let staircases=[];
let stairMeshGrps=new Map();
let _stairId=0;
let selSlab=null;

// Three.js setup
const scene=new THREE.Scene();
const renderer=new THREE.WebGLRenderer({canvas:document.getElementById('c3d'),antialias:true});
renderer.setPixelRatio(Math.min(devicePixelRatio,2));
renderer.shadowMap.enabled=true;
renderer.setClearColor(0x07080c);
renderer.toneMapping=THREE.ACESFilmicToneMapping;
renderer.toneMappingExposure=1.1;
const camera=new THREE.PerspectiveCamera(42,1,1,6000);
let sph={theta:-0.55, phi:0.72, r:1600};
let tgt=new THREE.Vector3(BW/2,0,BD/2);
let dragging=false, rDragging=false, lm={x:0,y:0};
let roomMeshes=[];
let floorGrps=[];
const raycaster=new THREE.Raycaster();
const mouse=new THREE.Vector2();
const dragPlane=new THREE.Plane(new THREE.Vector3(0,1,0),0);
const dragIntersect=new THREE.Vector3();
let dragOffset=new THREE.Vector3();

// Scene
scene.fog=new THREE.FogExp2(0x07080c,0.0006);
const gnd=new THREE.Mesh(new THREE.PlaneGeometry(6000,6000),new THREE.MeshStandardMaterial({color:0x050608,roughness:1}));
gnd.rotation.x=-Math.PI/2; gnd.position.y=-2; gnd.receiveShadow=true; scene.add(gnd);
const gridH=new THREE.GridHelper(4000,80,0x0d1018,0x0d1018); gridH.position.y=-1; scene.add(gridH);
const amb=new THREE.AmbientLight(0x7080aa,0.65); scene.add(amb);
const sun=new THREE.DirectionalLight(0xfff0d0,1.5);
sun.position.set(600,900,400); sun.castShadow=true; sun.shadow.mapSize.set(2048,2048);
sun.shadow.camera.left=-1200; sun.shadow.camera.right=1200; sun.shadow.camera.top=1200; sun.shadow.camera.bottom=-1200;
scene.add(sun);
const fill=new THREE.DirectionalLight(0x3050ff,0.3); fill.position.set(-400,400,600); scene.add(fill);

// Orbit controls (view only)
const c3d=document.getElementById('c3d');
c3d.addEventListener('mousedown',e=>{
  if(e.button===0) dragging=true;
  else if(e.button===2) rDragging=true;
  lm={x:e.clientX,y:e.clientY};
});
window.addEventListener('mouseup',()=>{dragging=false;rDragging=false;});
window.addEventListener('mousemove',e=>{
  if(dragging&&currentView==='3d'){
    sph.theta-=(e.clientX-lm.x)*0.006;
    sph.phi=Math.max(0.08,Math.min(Math.PI/2-0.05,sph.phi-(e.clientY-lm.y)*0.004));
    camUp();
  } else if(rDragging&&currentView==='3d'){
    const d=0.6;
    tgt.x-=Math.cos(sph.theta)*(e.clientX-lm.x)*d;
    tgt.z+=Math.sin(sph.theta)*(e.clientX-lm.x)*d;
    tgt.x+=Math.sin(sph.theta)*(e.clientY-lm.y)*d;
    tgt.z+=Math.cos(sph.theta)*(e.clientY-lm.y)*d;
    camUp();
  }
  lm={x:e.clientX,y:e.clientY};
});
c3d.addEventListener('wheel',e=>{
  sph.r=Math.max(150,Math.min(3500,sph.r+e.deltaY*1.2));
  camUp(); e.preventDefault();
},{passive:false});
c3d.addEventListener('contextmenu',e=>e.preventDefault());

// Touch support
let tc=[];
c3d.addEventListener('touchstart',e=>{tc=[...e.touches];},{passive:true});
c3d.addEventListener('touchmove',e=>{
  if(e.touches.length===1&&tc.length===1){
    sph.theta-=(e.touches[0].clientX-tc[0].clientX)*0.005;
    sph.phi=Math.max(0.08,Math.min(Math.PI/2-0.05,sph.phi-(e.touches[0].clientY-tc[0].clientY)*0.004));
    camUp();
  } else if(e.touches.length===2&&tc.length===2){
    const d0=Math.hypot(tc[0].clientX-tc[1].clientX,tc[0].clientY-tc[1].clientY);
    const d1=Math.hypot(e.touches[0].clientX-e.touches[1].clientX,e.touches[0].clientY-e.touches[1].clientY);
    sph.r=Math.max(150,Math.min(3500,sph.r-(d1-d0)*3)); camUp();
  }
  tc=[...e.touches]; e.preventDefault();
},{passive:false});

// Stub functions not needed in viewer
function rebuildRoomList(){}
function updateEmptyState(){}
function refreshFloorUI(){ const f=FLOORS.length; document.getElementById('sb-floors').textContent=f+' floor'+(f!==1?'s':''); }
function activateFloor(fi){ activeFloor=fi; }
function refreshOverlapState(){}
function refreshStairList(){}

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
function resetView(){ sph={theta:-0.55,phi:0.72,r:1600}; tgt.set(BW/2,FLOORS.length*FH/2||0,BD/2); camUp(); }
function topDown(){ sph.phi=0.05; sph.r=1100; camUp(); }
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

function autoSave(){ try{lsSet(AS_KEY,JSON.stringify(snapshot()));}catch(e){} pushUndo(); }

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



// Viewer-specific setView override
function setView(v){
  currentView=v;
  document.getElementById('btn3d').classList.toggle('active',v==='3d');
  document.getElementById('btnFloor').classList.toggle('active',v==='floor');
  if(v==='3d') resetView();
  else topDown();
}

// Animate
function resize(){
  const wrap=document.getElementById('wrap');
  renderer.setSize(wrap.clientWidth,wrap.clientHeight);
  camera.aspect=wrap.clientWidth/wrap.clientHeight;
  camera.updateProjectionMatrix();
}
window.addEventListener('resize',resize);

const clock=new THREE.Clock();
function animate(){
  requestAnimationFrame(animate);
  renderer.render(scene,camera);
}
animate();
camUp();
resize();

// ══ LOAD ACTIVE LAYOUT FROM SERVER ══
(async function(){
  try{
    const res=await fetch('../php/api/student/get_floorplan3d.php',{credentials:'include'});
    const data=await res.json();
    if(!data.success){
      document.getElementById('overlay-msg').style.display='none';
      document.getElementById('overlay-err').style.display='block';
      document.getElementById('overlay-err').textContent=data.message||'No layout available.';
      document.getElementById('overlay-back').style.display='inline-block';
      return;
    }
    restoreLayout(JSON.parse(data.layout_json));
    refreshFloorUI();
    resetView();
    // Show layout info badge
    const li=document.getElementById('layout-info');
    document.getElementById('layout-name').textContent=data.name||'Campus Map';
    document.getElementById('layout-date').textContent='Updated: '+new Date(data.updated_at).toLocaleDateString();
    li.style.display='block';
    // Hide overlay
    document.getElementById('viewer-overlay').classList.add('hidden');
    toast('✓ Campus map loaded');
  } catch(e){
    document.getElementById('overlay-msg').style.display='none';
    document.getElementById('overlay-err').style.display='block';
    document.getElementById('overlay-err').textContent='Failed to load campus map. Please try again.';
    document.getElementById('overlay-back').style.display='inline-block';
  }
})();
</script>
</body>
</html>
