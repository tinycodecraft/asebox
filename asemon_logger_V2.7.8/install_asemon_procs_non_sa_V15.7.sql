-- For V15.7ESD#2 and upper
-- This script script creates procedures used by asemon_logger when asemon login don't have sa_role
-- By default login 'asemon' is used in this script.
-- Edit this file and change @asemon_login variable if you want another login
set nocount on
set flushmessage on
declare @asemon_login varchar(30), @status int

select @asemon_login = 'asemon'               -- ****** Change this if needed ********

select @status=set_appcontext('asemon','asemon_login', @asemon_login)  -- save this variable in session context




-- Check ASE version
if  @@version_number < 15000
begin
    print "Wrong script for this ASE version"
    select syb_quit()
end


-- Check if login for asemon has been created
if not exists (select name from master..syslogins where name = @asemon_login)
begin
    print "Login '%1!' does not exists; create it with 'sp_addlogin' first.", @asemon_login
   	select syb_quit()
end



use sybsystemprocs
go



if object_id("sp_asemon_showplan") <> NULL
        drop proc sp_asemon_showplan
go

print "Create procedure sp_asemon_showplan"
go
create procedure sp_asemon_showplan
       @spid int = NULL,
       @batch_id int = NULL output,
       @context_id int = NULL output,
       @stmt_num int = NULL output
       with execute as owner
    as
    begin
    declare @spid_suid int, @my_suname varchar(32)
    declare @role_status int
    
       set nocount on
    
        if @spid = NULL
        begin
            print "Runs sp_showplan for a spid (session)."
            print "Usage: sp_asemon_showplan <spid-no> [, @batch_id , @context_id , @stmt_num ]"
            return -1
        end
    
      -- run sp_showplan
       exec sp_showplan @spid, @batch_id output, @context_id output, @stmt_num output
    
       return 0
    end
go
grant exec on sp_asemon_showplan		to mon_role
go





if object_id("sp_asemon_check_sa_role") <> NULL
        drop proc sp_asemon_check_sa_role
go

print "Create procedure sp_asemon_check_sa_role"
go
create procedure sp_asemon_check_sa_role
with execute as owner
    as
    begin
    declare @role_status int
    
       set nocount on
    
       select proc_role("sa_role")
    
       return 0
    end
go
grant exec on sp_asemon_check_sa_role		to mon_role
go




if object_id("sp_asemon_objstats") <> NULL
      drop proc sp_asemon_objstats
go
print "Create procedure sp_asemon_objstats"
go
if exists (select * from tempdb.dbo.sysobjects where name ='syslkstats' and uid=1)
        drop table tempdb.dbo.syslkstats
go
create procedure sp_asemon_objstats
       @cmd varchar(30) = null,
       @dbid int = null
with execute as owner
    as
    begin
    
       declare @role_status int
       
       set nocount on
    
    
       if @cmd = "init"
       begin
          dbcc traceon(1213) 
          if exists (select * from tempdb.dbo.sysobjects where name = 'syslkstats')
              drop table tempdb.dbo.syslkstats
                    
          create table tempdb.dbo.syslkstats(
                               dbid	 	smallint,
		       	       objid 	 	int,
		       	       lockscheme	smallint,
		       	       page_type		smallint,
		       	       stat_name		char(30),
		      	       stat_value	double precision)
		      	                 
       end
    
       if @cmd = "truncate"
       begin
           truncate table tempdb.dbo.syslkstats
       end
       
       if @cmd = "init_locks"
       begin
          dbcc object_stats(init_locks) 
       END
    
       if @cmd = "insert_locks"
       begin
          dbcc object_stats(insert_locks, @dbid )
       END
    
       if @cmd = "select"
       begin
       
           exec ("select 
             db_name(A.dbid),
             object_name(A.objid,A.dbid),
             case A.lockscheme when 1 then 'Allpages' when 2 then 'Datapages' when 3 then 'Datarows' end,
             A.page_type,
             A.stat_name,
             A.stat_value ,
             B.stat_value, 
             A.stat_value/B.stat_value
              from tempdb.dbo.syslkstats A , tempdb.dbo.syslkstats B
              where
              A.stat_name in (
             	'ex_pg_waittime',
             	'ex_row_waittime',
             	'sh_pg_waittime',
             	'sh_row_waittime',
             	'up_pg_waittime',
             	'up_row_waittime')
              and A.stat_value >0.0
              and A.dbid=B.dbid
              and A.objid=B.objid
              and A.page_type=B.page_type
              and (
              ( A.stat_name='ex_pg_waittime' and B.stat_name='ex_pg_waits')
              or
              ( A.stat_name='ex_row_waittime' and B.stat_name='ex_row_waits')
              or
              ( A.stat_name='sh_pg_waittime' and B.stat_name='sh_pg_waits')
              or
              ( A.stat_name='sh_row_waittime' and B.stat_name='sh_row_waits')
              or
              ( A.stat_name='up_pg_waittime' and B.stat_name='up_pg_waits')
              or
              ( A.stat_name='up_row_waittime' and B.stat_name='up_row_waits')
             )
              and B.stat_value>0
              order by A.stat_value desc ")
              
       END


    
       return 0
    end
go
grant exec on sp_asemon_objstats		to mon_role
go



if object_id("sp_asemon_sysmon ") <> NULL
      drop proc sp_asemon_sysmon 
go
print "Create procedure sp_asemon_sysmon"
go
create procedure sp_asemon_sysmon 
    @param varchar(10) = ""
with execute as owner
    as
    begin
    
       declare @role_status int
       
       set nocount on
    

    if @param="fld" 
    begin    
       dbcc monitor ("select","all","on")
       select 
       group_name,
       field_id,
       field_name
       from
       master..sysmonitors
       where group_name not like 'spinlock%'
       order by group_name,field_id
    end
    
    if @param=''
    begin
    
if object_id("tempdb..spinlocknames") is null
begin
   exec ("create table tempdb..spinlocknames (field_name varchar(80) not null, short_field_name varchar(3) not null)")
   exec ("create unique clustered index ic on tempdb..spinlocknames (field_name)")
   exec ("insert into tempdb..spinlocknames values ('Vdp_spin' , '2'                                               )")
   exec ("insert into tempdb..spinlocknames values ('ASTC_SPIN' , '5'                                              )")
   exec ("insert into tempdb..spinlocknames values ('fglockspins' , '10'                                           )")
   exec ("insert into tempdb..spinlocknames values ('DATACHG_SPIN' , '12'                                          )")
   exec ("insert into tempdb..spinlocknames values ('PTNCOND_SPIN' , '13'                                          )")
   exec ("insert into tempdb..spinlocknames values ('SITEBUF_SPIN' , '14'                                          )")
   exec ("insert into tempdb..spinlocknames values ('tablockspins' , '18'                                          )")
   exec ("insert into tempdb..spinlocknames values ('Ides Spinlocks' , '23'                                        )")
   exec ("insert into tempdb..spinlocknames values ('Pdes Spinlocks' , '24'                                        )")
   exec ("insert into tempdb..spinlocknames values ('addrlockspins' , '25'                                         )")
   exec ("insert into tempdb..spinlocknames values ('SSQLCACHE_SPIN' , '33'                                        )")
   exec ("insert into tempdb..spinlocknames values ('kdaio_spinlock' , '34'                                        )")
   exec ("insert into tempdb..spinlocknames values ('Des Upd Spinlocks' , '39'                                     )")
   exec ("insert into tempdb..spinlocknames values ('SMCD_spinlock[i]' , '45'                                      )")
   exec ("insert into tempdb..spinlocknames values ('default data cache' , '46'                                    )")
   exec ("insert into tempdb..spinlocknames values ('kdalloc_spinlock' , '47'                                      )")
   exec ("insert into tempdb..spinlocknames values ('Resource->hk_spin' , '49'                                     )")
   exec ("insert into tempdb..spinlocknames values ('Dbt->dbt_repl_spin' , '57'                                    )")
   exec ("insert into tempdb..spinlocknames values ('Ides Chain Spinlocks' , '58'                                  )")
   exec ("insert into tempdb..spinlocknames values ('Kernel->kaspinlock' , '59'                                    )")
   exec ("insert into tempdb..spinlocknames values ('Kernel->kbmempools' , '60'                                    )")
   exec ("insert into tempdb..spinlocknames values ('Kernel->kespinlock' , '61'                                    )")
   exec ("insert into tempdb..spinlocknames values ('Pdes Chain Spinlocks' , '62'                                  )")
   exec ("insert into tempdb..spinlocknames values ('SVRNAP_spinlock[i]' , '63'                                    )")
   exec ("insert into tempdb..spinlocknames values ('Networking_spinlock' , '69'                                   )")
   exec ("insert into tempdb..spinlocknames values ('Resource->rdbt_spin' , '70'                                   )")
   exec ("insert into tempdb..spinlocknames values ('Dbtable->dbt_seqspin' , '74'                                  )")
   exec ("insert into tempdb..spinlocknames values ('Resource->rdbts_spin' , '75'                                  )")
   exec ("insert into tempdb..spinlocknames values ('Kernel->erunqspinlock' , '81'                                 )")
   exec ("insert into tempdb..spinlocknames values ('Kernel->kpprocspin[i]' , '82'                                 )")
   exec ("insert into tempdb..spinlocknames values ('Resource->rtmpdb_spin' , '83'                                 )")
   exec ("insert into tempdb..spinlocknames values ('User Log Cache Spinlocks' , '84'                              )")
   exec ("insert into tempdb..spinlocknames values ('Kernel->kfio->irw_lock' , '86'                                )")
   exec ("insert into tempdb..spinlocknames values ('Kernel->kprunqspinlock' , '87'                                )")
   exec ("insert into tempdb..spinlocknames values ('Resource->rchatrm_spin' , '88'                                )")
   exec ("insert into tempdb..spinlocknames values ('Resource->rdesmgr_spin' , '89'                                )")
   exec ("insert into tempdb..spinlocknames values ('Resource->rpssmgr_spin' , '90'                                )")
   exec ("insert into tempdb..spinlocknames values ('Resource->rsysgam_spin' , '91'                                )")
   exec ("insert into tempdb..spinlocknames values ('Kernel->kalarm_spinlock' , '94'                               )")
   exec ("insert into tempdb..spinlocknames values ('Kernel->kslots_spinlock' , '95'                               )")
   exec ("insert into tempdb..spinlocknames values ('Resource->raccmeth_spin' , '96'                               )")
   exec ("insert into tempdb..spinlocknames values ('Resource->rmda_spinlock' , '97'                               )")
   exec ("insert into tempdb..spinlocknames values ('Resource->rpdesmgr_spin' , '98'                               )")
   exec ("insert into tempdb..spinlocknames values ('Resource->rprocmgr_spin' , '99'                               )")
   exec ("insert into tempdb..spinlocknames values ('Resource->rsqltext_spin' , '100'                              )")
   exec ("insert into tempdb..spinlocknames values ('Dbtable->dbt_pipemgrspin' , '103'                             )")
   exec ("insert into tempdb..spinlocknames values ('Dbtable->dbt_thresh_spin' , '104'                             )")
   exec ("insert into tempdb..spinlocknames values ('Kernel->kcsi_spinlock[i]' , '105'                             )")
   exec ("insert into tempdb..spinlocknames values ('Kernel->ksalloc_spinlock' , '106'                             )")
   exec ("insert into tempdb..spinlocknames values ('Resource->rcaps_spinlock' , '107'                             )")
   exec ("insert into tempdb..spinlocknames values ('Resource->rpdeshash_spin' , '108'                             )")
   exec ("insert into tempdb..spinlocknames values ('Resource->rwaittask_spin' , '109'                             )")
   exec ("insert into tempdb..spinlocknames values ('Kernel->kprocobj_spinlock' , '111'                            )")
   exec ("insert into tempdb..spinlocknames values ('Networkmemorypoolspinlock' , '112'                            )")
   exec ("insert into tempdb..spinlocknames values ('Resource->rdbtnextid_spin' , '113'                            )")
   exec ("insert into tempdb..spinlocknames values ('Resource->rproccache_spin' , '114'                            )")
   exec ("insert into tempdb..spinlocknames values ('Resource->rrdatetime_spin' , '115'                            )")
   exec ("insert into tempdb..spinlocknames values ('Resource->rgheapblock_spin' , '116'                           )")
   exec ("insert into tempdb..spinlocknames values ('Dbtable->dbt_xdesqueue_spin' , '117'                          )")
   exec ("insert into tempdb..spinlocknames values ('Dbtable.pfts_data.pfts_spin' , '118'                          )")
   exec ("insert into tempdb..spinlocknames values ('Kernel->kpsleepqspinlock[i]' , '119'                          )")
   exec ("insert into tempdb..spinlocknames values ('Kernel->kslistener_spinlock' , '120'                          )")
   exec ("insert into tempdb..spinlocknames values ('Resource->rlockobjpool_spin' , '121'                          )")
   exec ("insert into tempdb..spinlocknames values ('Resource->rqueryplan_spin[i]' , '122'                         )")
   exec ("insert into tempdb..spinlocknames values ('Dbtable->dbt_defpipebufgpspin' , '123'                        )")
   exec ("insert into tempdb..spinlocknames values ('Kernel->kssocktab_spinlock[i]' , '124'                        )")
   exec ("insert into tempdb..spinlocknames values ('Resource->rlocksemaphore_spin' , '125'                        )")
   exec ("insert into tempdb..spinlocknames values ('Resource->rlocksleeptask_spin' , '126'                        )")
   exec ("insert into tempdb..spinlocknames values ('Resource->runilibmutex_spin[i]' , '127'                       )")
   exec ("insert into tempdb..spinlocknames values ('Dbt->dbt_repl_context.repl_spinlock' , '128'                  )")
   exec ("insert into tempdb..spinlocknames values ('Dbtable->dbt_alsinfo.adi_plcflusher_queue_spin' , '129'       )")
   exec ("insert into tempdb..spinlocknames values ('Dbtable->dbt_alsinfo.adi_xls_writecomplete_queue_spin' , '130')")
   exec ("insert into tempdb..spinlocknames values ('COMP_SPIN' , '133'                                            )")
   exec ("insert into tempdb..spinlocknames values ('Dynmp_spin' , '134'                                           )")
   exec ("insert into tempdb..spinlocknames values ('ENCR_SPIN' , '135'                                            )")
   exec ("insert into tempdb..spinlocknames values ('Kernel Spinlock Spinlock' , '136'                             )")
   exec ("insert into tempdb..spinlocknames values ('Kernel->kbmemblocks' , '137'                                  )")
   exec ("insert into tempdb..spinlocknames values ('Kernel->kbmemstacks' , '138'                                  )")
   exec ("insert into tempdb..spinlocknames values ('Kernel->kbspinlock' , '139'                                   )")
   exec ("insert into tempdb..spinlocknames values ('Kernel->kcsi_factory_spinlock' , '140'                        )")
   exec ("insert into tempdb..spinlocknames values ('Kernel->kfio->foc_lock' , '141'                               )")
   exec ("insert into tempdb..spinlocknames values ('Kernel->kmemobjects' , '142'                                  )")
   exec ("insert into tempdb..spinlocknames values ('Kernel->kmspinlock' , '143'                                   )")
   exec ("insert into tempdb..spinlocknames values ('Kernel->kpspinlock' , '144'                                   )")
   exec ("insert into tempdb..spinlocknames values ('Kernel->kshbc_spinlock' , '145'                               )")
   exec ("insert into tempdb..spinlocknames values ('Kernel->kssocktab_spinlock[0]' , '146'                        )")
   exec ("insert into tempdb..spinlocknames values ('Kernel->kwt->kwt_memspinlock' , '147'                         )")
   exec ("insert into tempdb..spinlocknames values ('Kernel->kwt->kwt_spinlock' , '148'                            )")
   exec ("insert into tempdb..spinlocknames values ('Kernel->kxpserver_spinlock' , '149'                           )")
   exec ("insert into tempdb..spinlocknames values ('Kernel->rrtms_command_spinlock' , '150'                       )")
   exec ("insert into tempdb..spinlocknames values ('Kernel->rrtms_jvm_spinlock' , '151'                           )")
   exec ("insert into tempdb..spinlocknames values ('LDAP_SPIN' , '152'                                            )")
   exec ("insert into tempdb..spinlocknames values ('LMEMUSG_SPIN' , '153'                                         )")
   exec ("insert into tempdb..spinlocknames values ('NEXTAPMONDX_SPIN' , '154'                                     )")
   exec ("insert into tempdb..spinlocknames values ('Network memory pool spinlock' , '155'                         )")
   exec ("insert into tempdb..spinlocknames values ('RMEMLOG_SPINLOCK' , '156'                                     )")
   exec ("insert into tempdb..spinlocknames values ('RTMS_SPIN' , '157'                                            )")
   exec ("insert into tempdb..spinlocknames values ('Resource->ha_spin' , '158'                                    )")
   exec ("insert into tempdb..spinlocknames values ('Resource->maxscanthread_spin' , '159'                         )")
   exec ("insert into tempdb..spinlocknames values ('Resource->maxthread_spin' , '160'                             )")
   exec ("insert into tempdb..spinlocknames values ('Resource->qdb_spin' , '161'                                   )")
   exec ("insert into tempdb..spinlocknames values ('Resource->rals_info.ai_service_spin' , '162'                  )")
   exec ("insert into tempdb..spinlocknames values ('Resource->raudit_spin' , '163'                                )")
   exec ("insert into tempdb..spinlocknames values ('Resource->rdbt_ext_spin' , '164'                              )")
   exec ("insert into tempdb..spinlocknames values ('Resource->rdbt_xspin' , '165'                                 )")
   exec ("insert into tempdb..spinlocknames values ('Resource->rdbtddlcount_spin' , '166'                          )")
   exec ("insert into tempdb..spinlocknames values ('Resource->rdes_xspin' , '167'                                 )")
   exec ("insert into tempdb..spinlocknames values ('Resource->rdesidt_spin' , '168'                               )")
   exec ("insert into tempdb..spinlocknames values ('Resource->rdskbuf_spin' , '169'                               )")
   exec ("insert into tempdb..spinlocknames values ('Resource->rdumpdb_spin' , '170'                               )")
   exec ("insert into tempdb..spinlocknames values ('Resource->rerrpll_spin' , '171'                               )")
   exec ("insert into tempdb..spinlocknames values ('Resource->rexerlog_spin' , '172'                              )")
   exec ("insert into tempdb..spinlocknames values ('Resource->rgmemfrag' , '173'                                  )")
   exec ("insert into tempdb..spinlocknames values ('Resource->rlang_spin' , '174'                                 )")
   exec ("insert into tempdb..spinlocknames values ('Resource->rlmt_spin' , '175'                                  )")
   exec ("insert into tempdb..spinlocknames values ('Resource->rlockpromotion_spin' , '176'                        )")
   exec ("insert into tempdb..spinlocknames values ('Resource->rltctx_spin' , '177'                                )")
   exec ("insert into tempdb..spinlocknames values ('Resource->romnicurs_spin' , '178'                             )")
   exec ("insert into tempdb..spinlocknames values ('Resource->romnides_spin' , '179'                              )")
   exec ("insert into tempdb..spinlocknames values ('Resource->romnipss_spin' , '180'                              )")
   exec ("insert into tempdb..spinlocknames values ('Resource->rpage_xspin' , '181'                                )")
   exec ("insert into tempdb..spinlocknames values ('Resource->rprot_spin' , '182'                                 )")
   exec ("insert into tempdb..spinlocknames values ('Resource->rrdes_spin' , '183'                                 )")
   exec ("insert into tempdb..spinlocknames values ('Resource->rrm_spin' , '184'                                   )")
   exec ("insert into tempdb..spinlocknames values ('Resource->rsdesmgr_spin' , '185'                              )")
   exec ("insert into tempdb..spinlocknames values ('Resource->rslgroup_spin' , '186'                              )")
   exec ("insert into tempdb..spinlocknames values ('Resource->rslmgr_hash_spin' , '187'                           )")
   exec ("insert into tempdb..spinlocknames values ('Resource->rslmgr_spin' , '188'                                )")
   exec ("insert into tempdb..spinlocknames values ('Resource->rsrvdes_spin' , '189'                               )")
   exec ("insert into tempdb..spinlocknames values ('Resource->rsysind_spin' , '190'                               )")
   exec ("insert into tempdb..spinlocknames values ('Resource->rsysind_xspin' , '191'                              )")
   exec ("insert into tempdb..spinlocknames values ('Resource->rtmrng_spin' , '192'                                )")
   exec ("insert into tempdb..spinlocknames values ('Resource->runicache_spin' , '193'                             )")
   exec ("insert into tempdb..spinlocknames values ('Resource->rxact_xspin' , '194'                                )")
   exec ("insert into tempdb..spinlocknames values ('Resource->rxlsmempool_spin' , '195'                           )")
   exec ("insert into tempdb..spinlocknames values ('SQLDEBUG' , '196'                                             )")
   exec ("insert into tempdb..spinlocknames values ('Security Buffer Pool' , '197'                                 )")
   exec ("insert into tempdb..spinlocknames values ('TMPOBJ_SPIN' , '198'                                          )")
   exec ("insert into tempdb..spinlocknames values ('TRIG_SPIN' , '199'                                            )")
   exec ("insert into tempdb..spinlocknames values ('TXRCOLDES_SPIN' , '200'                                       )")
   exec ("insert into tempdb..spinlocknames values ('XDES_HASH_BUCKET_SPINLOCK' , '201'                            )")
   exec ("insert into tempdb..spinlocknames values ('XDES_SPIN' , '202'                                            )")
   exec ("insert into tempdb..spinlocknames values ('kdmirror_spinlock' , '203'                                    )")
   exec ("insert into tempdb..spinlocknames values ('kdvirtdisk_spinlock' , '204'                                  )")
   exec ("insert into tempdb..spinlocknames values ('Slgroup Spinlocks' , '205'                                    )")
   exec ("insert into tempdb..spinlocknames values ('QMEMUSG_SPIN' , '206'                                         )")
   exec ("insert into tempdb..spinlocknames values ('Kernel->ksmigrate_spinlock' , '207'                           )")
   exec ("insert into tempdb..spinlocknames values ('Kernel->kkern_resmem_spin' , '208'                            )")                                                    
   exec ("insert into tempdb..spinlocknames values ('Kernel->kkrmfrag_spin' , '209'                                )")                                                    
   exec ("insert into tempdb..spinlocknames values ('Kernel->kkrmtp_spin' , '210'                                  )")                                                    
   exec ("insert into tempdb..spinlocknames values ('Kernel->kkrmtrd_spin' , '211'                                 )")                                                    
   exec ("insert into tempdb..spinlocknames values ('Kernel->kkrmtsk_spin' , '212'                                 )")                                                    
   exec ("insert into tempdb..spinlocknames values ('Kernel->kkrmeng_spin' , '213'                                 )")                                                    
   exec ("insert into tempdb..spinlocknames values ('Kernel->kkrmdefq_spin' , '214'                                )")                                                    
   exec ("insert into tempdb..spinlocknames values ('Kernel->kkrmbc_spin' , '215'                                  )")                                                    
   exec ("insert into tempdb..spinlocknames values ('Kernel->kssocktab_spinlock' , '216'                           )")                                                    
   exec ("insert into tempdb..spinlocknames values ('Kernel->kcsi_mutex_list_spin' , '217'                         )")                                                    
   exec ("insert into tempdb..spinlocknames values ('Sybatomic_spinlock' , '218'                                   )")                                                    
   exec ("insert into tempdb..spinlocknames values ('Resource->dbrecdiag_spinlock' , '219'                         )")                                                    
   exec ("insert into tempdb..spinlocknames values ('DTUMEM_SPIN' , '220'                                          )")                                                    
   exec ("insert into tempdb..spinlocknames values ('CDFLTMEM_SPIN' , '221'                                        )")                                                    
   exec ("insert into tempdb..spinlocknames values ('CPINFOMEM_SPIN' , '222'                                       )")                                                    
   exec ("insert into tempdb..spinlocknames values ('GLBPWDVLT_SPIN' , '223'                                       )")                                                    
   exec ("insert into tempdb..spinlocknames values ('RAMEM_SPIN' , '224'                                           )")                                                    
   exec ("insert into tempdb..spinlocknames values ('RTPM_SPIN' , '225'                                            )")                                                    
   exec ("insert into tempdb..spinlocknames values ('Disk Controller Manager' , '226'                              )")                                                    
   exec ("insert into tempdb..spinlocknames values ('Network Controller Manager' , '227'                           )")                                                    
   exec ("insert into tempdb..spinlocknames values ('Ct-Lib Controller Manager' , '228'                            )")                                                    
   exec ("insert into tempdb..spinlocknames values ('Monitor spinlock' , '229'                                     )")                                                    
   exec ("insert into tempdb..spinlocknames values ('Kernel->kdynengspinlock' , '230'                              )")                                                    
   exec ("insert into tempdb..spinlocknames values ('Hashtable' , '231'                                            )")                                                    
   exec ("insert into tempdb..spinlocknames values ('Multimap' , '232'                                             )")                                                    
   exec ("insert into tempdb..spinlocknames values ('threadpool' , '233'                                           )")                                                    
   exec ("insert into tempdb..spinlocknames values ('syb_system_pool' , '234'                                      )")                                                    
   exec ("insert into tempdb..spinlocknames values ('Deferred Queue' , '235'                                       )")                                                    
   exec ("insert into tempdb..spinlocknames values ('syb_default_pool' , '236'                                     )")                                                    
   exec ("insert into tempdb..spinlocknames values ('global sched' , '237'                                         )")                                                    
   exec ("insert into tempdb..spinlocknames values ('Sched Q' , '238'                                              )")                                                    
   exec ("insert into tempdb..spinlocknames values ('syb_blocking_pool' , '239'                                    )")                                                    
   exec ("insert into tempdb..spinlocknames values ('CtlibController' , '240'                                      )")                                                    
   exec ("insert into tempdb..spinlocknames values ('NetController' , '241'                                        )")                                                    
   exec ("insert into tempdb..spinlocknames values ('DiskController' , '242'                                       )")                                                    
   exec ("insert into tempdb..spinlocknames values ('imdb_cache' , '243'                                           )")                                                    
   exec ("insert into tempdb..spinlocknames values ('inmemrory_cache' , '244'                                      )")                                                    
   exec ("insert into tempdb..spinlocknames values ('Socktab Spinlock[i]' , '245'                                  )")                                                    
   exec ("insert into tempdb..spinlocknames values ('Resource->jst_info.jst_spin' , '246'                          )")                                                    
   exec ("insert into tempdb..spinlocknames values ('CTINFO SPIN' , '247'                                          )")                                                    
   exec ("insert into tempdb..spinlocknames values ('Resource->rpmctrl_spin' , '248'                               )")
   exec ("insert into tempdb..spinlocknames values ('JAVA_PERVM_SP' , '249'                                        )")
   exec ("insert into tempdb..spinlocknames values ('JRESOURCE_SPIN' , '250'                                       )")
   exec ("insert into tempdb..spinlocknames values ('JRES_POOLS_SPIN' , '251'                                      )")
   exec ("insert into tempdb..spinlocknames values ('JAVA_SUBSYSTEM_SP' , '252'                                    )")
   exec ("insert into tempdb..spinlocknames values ('JAVA_PROC_BLOCK_SP' , '253'                                   )")
   exec ("insert into tempdb..spinlocknames values ('JAVA_GLOBAL_FIXED_POOL_SP' , '254'                            )")

end

dbcc monitor ("select","all","on")
dbcc monitor ("select","spinlock_s","on")
dbcc monitor ("sample","all","on")
dbcc monitor ("sample","spinlock_s","on")
dbcc monitor ("sample","spinlock_p","on")
dbcc monitor ("sample","spinlock_w","on")

exec ("
select
grpname=
rtrim(
case
when  group_name='access'         then 'C0'  
when  group_name='alloc'          then 'C1'
when  group_name='astc'           then 'C2'
when  group_name='btree'          then 'C3'
when  group_name='config'         then 'C4'
when  group_name='control'        then 'C5'
when  group_name='dbcc'           then 'C6'
when  group_name='dbtable'        then 'C7'
when  group_name='descriptor'     then 'C8'
when  group_name='dfl'            then 'C9'
when  group_name='dolaccess'      then 'F0'
when  group_name='dolspace_mgmt'  then 'F1'
when  group_name='dump'           then 'F2'
when  group_name='ecache'         then 'F3'
when  group_name='housekeeper'    then 'F4'
when  group_name='kernel'         then 'F5'
when  group_name='latch'          then 'F6'
when  group_name='lock'           then 'F7'
when  group_name='mda'            then 'F8'
when  group_name='memory'         then 'F9'
when  group_name='monitor_access' then 'G0'
when  group_name='multdb'         then 'G1'
when  group_name='network'        then 'G2'
when  group_name='parallel'       then 'G3'
when  group_name='procmgr'        then 'G4'
when  group_name='remote'         then 'G5'
when  group_name='resmgr'         then 'G6'
when  group_name='resource_stats' then 'G7'
when  group_name='sdesmgr'        then 'G8'
when  group_name='sysind'         then 'G9'
when  group_name='textmgr'        then 'H0'
when  group_name='utils'          then 'H1'
when  group_name='xact'           then 'H2'
when  group_name='xls'            then 'H3'
when  group_name='sysptn'         then 'H4'
when  group_name='login'          then 'H5'
when  group_name like 'spinlock_p%'   then 'P'
when  group_name like 'spinlock_w%'   then 'W'
when  group_name like 'spinlock_s%'   then 'S'
when  substring(group_name,1,5)='disk_'       then 'D'+right(group_name,datalength(group_name)-5)
when  substring(group_name,1,9)='repagent_'   then 'A'+right(group_name,datalength(group_name)-9)
when  substring(group_name,1,7)='engine_'     then 'E'+right(group_name,datalength(group_name)-7)
when  substring(group_name,1,10)='eresource_' then 'R'+right(group_name,datalength(group_name)-10)
when  substring(group_name,1,7)='buffer_'     then 'B'+right(group_name,datalength(group_name)-7)
else group_name
end
),
field_id,
fldname,
d_value,
value
from 
(
select group_name, field_id, 

fldname=convert(varchar(80), 
    case when group_name in ('spinlock_p_0', 'spinlock_w_0', 'spinlock_s_0', 'spinlock_p', 'spinlock_w', 'spinlock_s') 
    then case when S.short_field_name is null then M.field_name else short_field_name end 
    else null end), 

d_value= case 
         when group_name in ('config', 'resource_stats', 'control')
                 or M.field_name like 'max%'
                 or M.field_name like '%hwm' 
         then 0
         else value
         end,

value=  case 
         when group_name in ('config', 'resource_stats', 'control')
                 or M.field_name like 'max%'
                 or M.field_name like '%hwm' 
         then value
         else convert(int,null)
         end
from master..sysmonitors M left outer join tempdb..spinlocknames S on rtrim(M.field_name)=S.field_name
where value !=0
) sysmon
union all
select 'Z',0,null,datediff(ss,'01/01/2010',getdate()), null
"
)

    
    
    end
    
    
    
       return 0
    end
go
grant exec on sp_asemon_sysmon		to mon_role
go





if object_id("sp_asemon_fragmentation") <> NULL
        drop proc sp_asemon_fragmentation
go

print "Create procedure sp_asemon_fragmentation"
go
create procedure sp_asemon_fragmentation
       @minpages varchar(10) = null
with execute as owner
    as
    begin
    
       declare 	@role_status int,
				@dbid int,
				@dbname sysname,
				@rowcnt int
		set nocount on
           
		if @minpages is null
		begin
			print "Usage restricted to asemon_logger"
			return -1
		end

    
        create table #fragment (
            dbname            sysname       null,
            owner             sysname       null,
            tabname           sysname       null,
            indname           sysname       null,
            indid             int           null,
            lockmode          varchar(10)   null,
            clu               char(3)       null,
            pagecnt           unsigned int           null,
            leafcnt           unsigned int           null,
            emptypgcnt        unsigned int           null,
            Rowcnt            numeric(10,0) null, 
            Forwardrowcnt     numeric(10,0) null, 
            Delrowcnt         numeric(10,0) null, 
            dpageCR           numeric(10,2) null, 
            ipageCR           numeric(10,2) null, 
            drowCR            numeric(10,2) null, 
            page_utilization  numeric(10,2) null, 
            space_utilization numeric(10,2) null, 
            largeIO_eff       numeric(10,2) null, 
            actual_datapages  int           null,
            actual_indexpages int           null,
            reserved_pages    int           null,
            Dpage_utilization numeric(10,2) null
        )
        
        select dbid, name
        into #db_cursor
        from master..sysdatabases
        where name != 'tempdb'
        and status&1   != 1   -- database upgrading
        and status&32   != 32   -- database created for load
        and status&64   != 64   -- database recovery
        and status&256   != 256   -- database suspect
        and status&4096   != 4096   -- single user
        
        and status2&16   != 16   -- database offline
        and status2&32   != 32   -- database offline
        and status2&512   != 512   -- database currently upgrading
        
        and status3&2   != 2   -- ignore proxy database
        and status3&4   != 4   -- ignore has proxy database
        and status3&8   != 8   -- database in shutdown
        and status3&256 != 256 -- ignore user created tempdb
        and status3&8192 != 8192 -- drop database in progress
        and status3&4194304 != 4194304 -- ignore archive databases
        
        order by name
        
        set rowcount 1
        select @dbid=dbid, @dbname=name from #db_cursor
        select @rowcnt=@@rowcount
        delete #db_cursor where dbid=@dbid
        set rowcount 0
        
        while @rowcnt = 1
        begin
           if @@version_as_integer < 15000
           begin
                exec (
					"insert into #fragment                          															"+
					"select '" +
					 @dbname +"' ,  owner=U.name,                     															"+
					"	tabname	=O.name,                            															"+
					"	indname	=I.name,                            															"+
					"	indid	=S.indid,                           															"+
					"        lockmode = case when sysstat2&8192=8192   then 'ALLPAGES' 											"+
					"                    when sysstat2&16384=16384 then 'DATAPAGES'    											"+              
					"                    when sysstat2&32768=32768 then 'DATAROWS'     											"+
					"                    else 'ALLPAGES'            															"+
					"                   end,                        															"+
					"	clu	=case when I.status&16=16 OR I.status2&512=512 then 'clu' else '' end,								"+
					"	S.pagecnt,                                  															"+
					"	S.leafcnt,                                  															"+
					"	S.emptypgcnt,                               															"+
					"	Rowcnt	=convert(numeric(10,0),S.rowcnt),   															"+
					"	Forwardrowcnt=convert(numeric(10,0), S.forwrowcnt),   													"+
					"	Delrowcnt=convert(numeric(10,0),S.delrowcnt),    														"+
					"	dpageCR=convert (numeric(10,2),derived_stat(@dbname+'.'+U.name+'.'+O.name, S.indid, 'dpcr')), 			"+
					"	ipageCR=convert (numeric(10,2),derived_stat(@dbname+'.'+U.name+'.'+O.name, S.indid, 'ipcr')), 			"+
					"	drowCR=convert (numeric(10,2),derived_stat(@dbname+'.'+U.name+'.'+O.name, S.indid, 'drcr')), 			"+
					"	page_utilization= null,                    								 								"+
					"	space_utilization=convert (numeric(10,2),derived_stat(@dbname+'.'+U.name+'.'+O.name, S.indid, 'sput')),"+
					"	largeIO_eff=convert (numeric(10,2),derived_stat(@dbname+'.'+U.name+'.'+O.name, S.indid, 'lgio')), 		"+
					"   actual_datapages = null /* data_pgs (@dbid, I.id, I.doampg)*/, 										"+
					"   actual_indexpages= null /* data_pgs (@dbid, I.id, I.ioampg)*/,											"+
					"   reserved_pages = null,  																					"+
          " Dpage_utilization = convert (numeric(10,2),                                                            "+
          "  case when S.indid in (0,1) then                                                                       "+
          "    case when sysstat2&16384=16384 or sysstat2&32768=32768 then                                         "+
          "             ceiling(1.*case when S.rowcnt=0 then 1 else S.rowcnt end / floor((@@maxpagesize -46)/((case when I.maxlen<10 then 10 else I.maxlen end)+2))) / S.pagecnt"+
          "          else"+
          "             ceiling(1.*case when S.rowcnt=0 then 1 else S.rowcnt end / case when floor((@@maxpagesize -32)/(I.maxlen+2)) > 255 then 255 else floor((@@maxpagesize -32)/(I.maxlen+2)) end) / S.pagecnt"+
          "    end"+
          "  else null end)"+
					"	from (select id, indid, pagecnt=sum(1.*pagecnt), leafcnt=sum(1.*leafcnt), emptypgcnt=sum(1.*emptypgcnt),"+
					" 	rowcnt=sum(1.*rowcnt), forwrowcnt=sum(1.*forwrowcnt),delrowcnt=sum(1.*delrowcnt) 						"+
					"       from "+@dbname+"..systabstats           															"+
					"       group by id,indid                       															"+
					"      ) S, "+@dbname+"..sysindexes I, "+@dbname+"..sysobjects O, "+@dbname+"..sysusers U 				"+
					"where S.id=I.id                                															"+
					"and S.indid=I.indid                            															"+
					"and S.id=O.id    and O.uid=U.uid               															"+
					"and O.sysstat2&1024 = 0  /* skip proxy tables  */  														"+
					"and O.sysstat2&2048 = 0  /* skip existing tables */ 														"+
					"and (S.pagecnt+S.leafcnt > " + @minpages +" )    															"+
					"and S.id!=8 /* don't take syslogs  */  																	"
                )
           end
           else
           begin
				exec (
					"insert into #fragment                                                                                    "+
					"select                                                                                                   "+
					"   @dbname, owner=U.name,                                                                              "+
					"	tabname	=O.name,                                                                                      "+
					"	indname	=I.name,                                                                                      "+
					"	indid	=S.indid,                                                                                     "+
					"   lockmode = case when sysstat2&8192=8192   then 'ALLPAGES'                                             "+
					"                    when sysstat2&16384=16384 then 'DATAPAGES'                                           "+
					"                    when sysstat2&32768=32768 then 'DATAROWS'                                            "+
					"                    else 'ALLPAGES'                                                                      "+
					"               end,                                                                                      "+
					"	clu	=case when I.status&16=16 OR I.status2&512=512 then 'clu' else '' end,                        	  "+
					"	S.pagecnt,                                                                                            "+
					"	S.leafcnt,                                                                                            "+
					"	S.emptypgcnt,                                                                                         "+
					"	Rowcnt	=convert(numeric(10,0),S.rowcnt),                                                             "+
					"	Forwardrowcnt=convert(numeric(10,0), S.forwrowcnt),                                                   "+
					"	Delrowcnt=convert(numeric(10,0),S.delrowcnt),                                                         "+
					"	dpageCR=convert (numeric(10,2),derived_stat(@dbname+'.'+U.name+'.'+O.name, S.indid, 'dpcr')),        "+
					"	ipageCR=convert (numeric(10,2),derived_stat(@dbname+'.'+U.name+'.'+O.name, S.indid, 'ipcr')),        "+
					"	drowCR=convert (numeric(10,2),derived_stat(@dbname+'.'+U.name+'.'+O.name, S.indid, 'drcr')),         "+
					"	page_utilization = null /* convert (numeric(10,2),                                                    "+
					"                         case when I.indid > 1 or reserved_pages(@dbid, I.id, 0) = 0 then null           "+
					"                              else 100.*data_pages(@dbid, I.id, 0) / reserved_pages(@dbid, I.id, 0) end)*/,"+
					"	space_utilization=convert (numeric(10,2),derived_stat(@dbname+'.'+U.name+'.'+O.name, S.indid, 'sput')),"+
					"	largeIO_eff=convert (numeric(10,2),derived_stat(@dbname+'.'+U.name+'.'+O.name, S.indid, 'lgio')),      "+
					"   actual_datapages = null /* data_pages (@dbid, I.id, case when I.indid in (0,1) then 0 else -1 end) */,"+
					"   actual_indexpages= null /*data_pages (@dbid, I.id, case when I.indid in (0,1) then 1 else I.indid end) */,"+
					"   reserved_pages   = null, /*case when I.indid != 1 then reserved_pages (@dbid, I.id, I.indid)           "+
					"                                else reserved_pages (@dbid, I.id, 0)+reserved_pages (@dbid, I.id, 1) end */"+
          " Dpage_utilization = convert (numeric(10,2),                                                            "+
          "  case when S.indid in (0,1) then                                                                       "+
          "    case when sysstat2&16384=16384 or sysstat2&32768=32768 then                                         "+
          "             ceiling(1.*case when S.rowcnt=0 then 1 else S.rowcnt end / floor((@@maxpagesize -46)/((case when I.maxlen<10 then 10 else I.maxlen end)+2))) / S.pagecnt"+
          "          else"+
          "             ceiling(1.*case when S.rowcnt=0 then 1 else S.rowcnt end / case when floor((@@maxpagesize -32)/(I.maxlen+2)) > 255 then 255 else floor((@@maxpagesize -32)/(I.maxlen+2)) end) / S.pagecnt"+
          "    end"+
          "  else null end)"+
					"from (select id, indid, pagecnt=sum(1.*pagecnt), leafcnt=sum(1.*leafcnt), emptypgcnt=sum(1.*emptypgcnt), "+
					"            rowcnt=sum(1.*rowcnt), forwrowcnt=sum(1.*forwrowcnt),delrowcnt=sum(1.*delrowcnt)             "+
					"       from "+@dbname+"..systabstats                                                                    "+
					"       group by id,indid                                                                                 "+
					"      ) S, "+@dbname+"..sysindexes I, "+@dbname+"..sysobjects O, "+@dbname+"..sysusers U              "+
					"where S.id=I.id                                                                                          "+
					"and S.indid=I.indid                                                                                      "+
					"and S.id=O.id    and O.uid=U.uid                                                                         "+
					"and O.sysstat2&1024 = 0  /* skip proxy tables */                                                         "+
					"and O.sysstat2&2048 = 0  /* skip existing tables */                                                      "+
					"and (S.pagecnt+S.leafcnt > " + @minpages +"  )                                                                 "+
					"and S.id!=8 /* don't take syslogs */                                                                     "
				)
            end
           
            set rowcount 1
            select @dbid=dbid, @dbname=name from #db_cursor
            select @rowcnt=@@rowcount
            delete #db_cursor  where dbid=@dbid
            set rowcount 0
        end
        select * from #fragment
        drop table #fragment
        drop table #db_cursor

            
    
       return 0
    end
go
grant exec on sp_asemon_fragmentation		to mon_role
go




if object_id("sp_asemon_monconf") <> NULL
      drop proc sp_asemon_monconf
go
print "Create procedure sp_asemon_monconf"
go
create procedure sp_asemon_monconf
with execute as owner
    as
    begin
    
       declare @role_status int
       
       set nocount on
    
    
       exec sp_monitorconfig 'all'    

    
       return 0
    end
go
grant exec on sp_asemon_monconf		to mon_role
go








declare @asemon_login varchar(30)
select @asemon_login=get_appcontext('asemon','asemon_login')
print "Create user '%1!' in databases", @asemon_login
go
declare c cursor for
    select name from master..sysdatabases
    where
    status&1   != 1   -- database upgrading
    and status&32   != 32   -- database created for load
    and status&64   != 64   -- database recovery
    and status&256   != 256   -- database suspect
    and status&4096   != 4096   -- single user

    and status2&16   != 16   -- database offline
    and status2&32   != 32   -- database offline
    and status2&512   != 512   -- database currently upgrading

    and status3&2   != 2   -- ignore proxy database
    and status3&4   != 4   -- ignore has proxy database
    and status3&8   != 8   -- databse in shutdown
    and status3&8192 != 8192 -- drop database in progress
    and status3&4194304 != 4194304 -- ignore archive databases
    order by name
go
declare @dbname varchar(30), @asemon_login varchar(30)

select @asemon_login=get_appcontext('asemon','asemon_login')
open c
fetch c into @dbname
while @@sqlstatus=0
begin
	print "Add user '%1!' to database '%2!'", @asemon_login, @dbname
    exec ("if exists (select * from "+@dbname+"..sysusers where name = '"+@asemon_login+"') 
                  exec "+@dbname+"..sp_dropuser "+@asemon_login+"
            if exists (select * from "+@dbname+"..sysalternates where suid=suser_id('"+@asemon_login+"') )
                  exec "+@dbname+"..sp_dropalias "+@asemon_login+"
            exec "+@dbname+"..sp_adduser "+@asemon_login+", "+@asemon_login)

    fetch c into @dbname
end
close c
deallocate cursor c
go

if object_id("sp_asemon_procs_version") <> NULL
      drop proc sp_asemon_procs_version 
go
create procedure sp_asemon_procs_version 
with execute as owner
as
select 2760
go
grant exec on sp_asemon_procs_version to mon_role
go
