#!/usr/bin/env python3
"""Protect native loader bytes and hashes across install/reinstall/rollback."""
from pathlib import Path
import hashlib,os,subprocess,tempfile
root=Path(__file__).resolve().parents[1]
def check(ok,label):
    if not ok:raise AssertionError(label)
    print('PASS',label)
def digest(p):return hashlib.sha256(p.read_bytes()).hexdigest()
def run(script,admin,backups,*args,success=True):
    env=dict(os.environ,MKAUTH_ADMIN=str(admin),MKAUTH_BACKUP_ROOT=str(backups))
    result=subprocess.run(['sh',str(root/'installers'/script),*map(str,args)],env=env,capture_output=True,text=True)
    if success and result.returncode:raise RuntimeError(result.stdout+result.stderr)
    if not success and result.returncode==0:raise AssertionError('unsafe directory link accepted')
    return result
with tempfile.TemporaryDirectory(prefix='mkauth-symlink-test-') as directory:
    temp=Path(directory).resolve();admin=temp/'admin';addons=admin/'addons';addons.mkdir(parents=True)
    native=temp/'global';native.mkdir();loader=native/'addons.inc.hhvm'
    original=b'<?php /* native loader fixture - must never be overwritten */\n'
    loader.write_bytes(original);loader_hash=digest(loader)
    cfg=native/'report-config.php';cfg.write_bytes(b'<?php $cfg_port_olt = true;\n');cfg_hash=digest(cfg)
    native_menu=native/'addon.js';native_menu.write_bytes(b'// unrelated menu fixture\n');menu_hash=digest(native_menu)
    mapdir=addons/'caixas';mapdir.mkdir();(mapdir/'addons.class.php').symlink_to(loader)
    report=addons/'rel_cto_cad_cli';report.mkdir();(report/'addons.class.php').symlink_to(loader)
    (report/'VERSION').symlink_to(loader);(report/'config.php').symlink_to(cfg)
    menu=addons/'addon.js';menu.symlink_to(native_menu)
    first=temp/'first-backup';run('install.sh',admin,first)
    check(loader.read_bytes()==original and digest(loader)==loader_hash,'global loader content and SHA256 unchanged after upgrade')
    check(not (report/'addons.class.php').is_symlink(),'report bootstrap replaces destination symlink')
    check(not (report/'VERSION').is_symlink() and (report/'VERSION').read_text().strip()=='1.1.55','VERSION symlink is replaced before writing')
    check((report/'config.php').is_symlink() and digest(cfg)==cfg_hash,'existing config link preserved without writing referent')
    check(not menu.is_symlink() and digest(native_menu)==menu_hash,'menu link detached before editing; global menu unchanged')
    # Recreate the MK-AUTH loader link as happens during native maintenance.
    (report/'addons.class.php').unlink();(report/'addons.class.php').symlink_to(loader)
    repeat=temp/'repeat-backup';run('install.sh',admin,repeat)
    check(digest(loader)==loader_hash and digest(native_menu)==menu_hash and digest(cfg)==cfg_hash,'native hashes unchanged after reinstall')
    # Rollback must replace a live destination link, not overwrite its target.
    menu.unlink();menu.symlink_to(native_menu)
    run('rollback.sh',admin,temp/'unused',next(repeat.iterdir()))
    check(digest(loader)==loader_hash and digest(native_menu)==menu_hash,'native hashes unchanged after reinstall rollback')
    check((report/'addons.class.php').is_symlink(),'rollback restores original report link node')
    run('rollback.sh',admin,temp/'unused',next(first.iterdir()))
    check(digest(loader)==loader_hash and loader.read_bytes()==original and digest(cfg)==cfg_hash,'native content and hashes unchanged after full rollback')
    check(menu.is_symlink() and digest(native_menu)==menu_hash,'full rollback restores original menu symlink without changing target')
    # An overlay must also refuse symlinked directories, not only leaf files.
    outside=temp/'external-js';outside.mkdir();sentinel=outside/'sentinel';sentinel.write_text('unchanged')
    (report/'js').symlink_to(outside,target_is_directory=True)
    before=digest(loader)
    run('install.sh',admin,temp/'unsafe-backup',success=False)
    check(sentinel.read_text()=='unchanged' and digest(loader)==before,'symlinked report asset directory rejected before mutations')
