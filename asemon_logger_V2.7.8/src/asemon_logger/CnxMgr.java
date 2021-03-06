/*
 * CnxMgr.java
 *
 * Created on 12 septembre 2007, 10:58
 *
 * <p>CnxMgr</p>
 * <p>class managing connections</p>
 * <p>Copyright: Jean-Paul Martin (jpmartin@sybase.com) Copyright (c) 2007</p>
 * @version 2.7.8
 */

package asemon_logger;
import com.sybase.jdbcx.*;
import java.sql.*;
import java.util.*;
import java.util.concurrent.Semaphore;


public class CnxMgr {

    // Pool of archive connections;
    static ArchCnxPool archCnxPool;

    /** Creates a new instance of CnxMgr */
    public CnxMgr() {
        archCnxPool = new ArchCnxPool();
    }
    



    /*
     * ArchCnx : class usedConnections to manage a connection to the archive server (only ASE supported for archive conn)
     *
     */
    class ArchCnx {
        // Connection handle to the archive server
        Connection archive_conn=null;
//        boolean first_time;  // True if this is the first time the connection is used
        long waitedFor;  // Time in ms spent waiting for a free cnx from the pool
        long startUse;   // System time when this cnx started to be used (include connexion establishment, if not yet connected)
        boolean isConnected; // True if the JDBC connection is open

        ArchCnx() {
            isConnected =false;
        }

    }

    /*
     * ArchCnxPool : class for managing pool of ArchCnx connections
     *  (size is currently hardcoded to 5)
     */

    class ArchCnxPool {
       private final int MAX_AVAILABLE = Asemon_logger.archive_poolsize;
       private final Semaphore available = new Semaphore(MAX_AVAILABLE, true);
       protected ArchCnx[] archConnections = new ArchCnx[MAX_AVAILABLE];
       protected boolean[] usedConnections = new boolean[MAX_AVAILABLE];

       public ArchCnxPool () {
           // Initialize arch conn descriptors
           for (int i=0; i<MAX_AVAILABLE; i++)
               archConnections[i] = new ArchCnx();
       }

       public ArchCnx getArchCnx(boolean waitFlag) throws Exception {
           ArchCnx aArchCnx;
           long startWait = System.currentTimeMillis();

           // Get a connection from the pool
           available.acquire();
           aArchCnx=getNextAvailableItem();

           aArchCnx.startUse = System.currentTimeMillis();
           long waitedForArchCnx =  aArchCnx.startUse - startWait;
           aArchCnx.waitedFor = waitedForArchCnx;
           if (waitedForArchCnx > 10000)
               Asemon_logger.printmess  ("getArchCnx : waited more than 10 s for a free cnx. Increase pool size.");
           // Open this connection if not already open
           if ((aArchCnx.archive_conn == null ) || (aArchCnx.archive_conn.isClosed() ) )
               aArchCnx.isConnected = connect_archive_SRV(aArchCnx, waitFlag);
           //aArchCnx.first_time = false;
           return aArchCnx;
       }

       public long putArchCnx(ArchCnx aArchCnx) {
           long timeUsed = System.currentTimeMillis() - aArchCnx.startUse;
           if (markAsUnused(aArchCnx))
               available.release();

           return timeUsed;
       }


       protected synchronized ArchCnx getNextAvailableItem() {
         for (int i = 0; i < MAX_AVAILABLE; ++i) {
           if (!usedConnections[i]) {
              usedConnections[i] = true;
              Asemon_logger.DEBUG("Get archive conn "+ i);
              return archConnections[i];
           }
         }
         return null; // not reached
       }

       protected synchronized boolean markAsUnused(ArchCnx item) {
         for (int i = 0; i < MAX_AVAILABLE; ++i) {
           if (item == archConnections[i]) {
              if (usedConnections[i]) {
                usedConnections[i] = false;
                Asemon_logger.DEBUG("Free archive conn "+i);
                return true;
              } else
                return false;
           }
         }
         return false;
       }

    }





   /* Old checkPasswd method
   static String checkPasswd (String servername, String username, String input_password, String question, boolean verbose)
   {
   	String pw;
	if (input_password==null ) {
		// Check if password exists in passwords file
		pw=Asemon_logger.pfm.getPassword(servername, username);
		if (pw==null) {
		   // not found in passwords file, ask to user
		   System.out.print (question);
		   pw=Password.getPassword();
		   // save this password in passwords file
		   Asemon_logger.pfm.addPassword(servername, username, pw);
                   Asemon_logger.printmess ("Password added in passwords file for '" +servername+"."+username+"'");
		}
		else 
                   if (verbose) Asemon_logger.printmess (Thread.currentThread().getName() +" - Using password from passwords file for '" +servername+"."+username+"'");
                return pw;
	}
	else {
	    // 	Check if password already exists in passwords file
	    pw=Asemon_logger.pfm.getPassword(servername, username);
	    if (pw == null) {
	        // not found in passwords file, add password to passwords file
	        Asemon_logger.pfm.addPassword(servername, username, input_password);
                Asemon_logger.printmess ("Password added in passwords file for '" +servername+"."+username+"'");
	    }
	    else 
	        // check if passwords are differents
	        if ( ! pw.equals(input_password) ) {
	           // YES, update password file
                   Asemon_logger.pfm.updPassword(servername, username, input_password);
                   Asemon_logger.printmess ("Password updated in passwords file for '" +servername+"."+username+"'");
                }
	    return input_password;
	}
   }
   */

    static String checkPasswd (String servername, String username, String question, boolean verbose)
    {
   	    String pw;
		// Check if password exists in passwords file
		pw=Asemon_logger.pfm.getPassword(servername, username);
		if (pw==null) {
		    // not found in passwords file, ask to user
		    System.out.print (question);
		    pw=Password.getPassword();
		    // save this password in passwords file
		    Asemon_logger.pfm.addPassword(servername, username, pw);
            Asemon_logger.printmess ("Password added in passwords file for '" +servername+"."+username+"'");
		}
		else
            if (verbose) Asemon_logger.printmess ("Using password from passwords file for '" +servername+"."+username+"'");
        return pw;

   }

   static Connection connectSRV(String user, String srv, String db, String charset, String question, boolean verbose, String appName, Boolean useKerberos, Boolean displayErrors, int packet_size) {
    Connection cnx=null;
    String passwd=null;

    if (verbose) Asemon_logger.printmess ("Try to connect to srv : "+srv);
    SybSrvAddress sAdd = SybDirectory.getServerAddress(srv);
    if (sAdd ==null) {
    	Asemon_logger.printmess  ("connectSRV : ERROR Server "+srv+" does not exists in interfaces or SQL.INI file");
    	return null;
    }
    if (verbose) Asemon_logger.printmess ("Srv found in interfaces or SQL.INI file. Host="+sAdd.host +" Port="+sAdd.port);

    if (! useKerberos)
        passwd = CnxMgr.checkPasswd (srv, user, question , verbose);

    try {
        Class.forName("com.sybase.jdbc4.jdbc.SybDriver").newInstance();

        Properties props = new Properties();
        if (! useKerberos) {
            props.put("user", user);
            props.put("password", passwd);
        }

        if (appName != null) props.put("APPLICATIONNAME", appName);

//        props.put("JCONNECT_VERSION","6");
//        props.put("JCONNECT_VERSION","6.05");
        props.put("JCONNECT_VERSION","7.0");


        if (packet_size > 0)
            props.put("PACKETSIZE",String.valueOf(packet_size));

        props.put("USE_METADATA","FALSE");
        props.put("DYNAMIC_PREPARE","FALSE");

// Removed the next setting since it is incompatible with ENABLE_BULK_LOAD
//        props.put("ESCAPE_PROCESSING_DEFAULT","FALSE");

//        props.put("IS_CLOSED_TEST","INTERNAL");
        props.put("IS_CLOSED_TEST","select 1");

        if (!Asemon_logger.disableBulkLoad) {
            //props.put("ENABLE_BULK_LOAD","TRUE");
            props.put("ENABLE_BULK_LOAD","ARRAYINSERT_WITH_MIXED_STATEMENTS");
        }
        else {
            props.put("ENABLE_BULK_LOAD","FALSE");
        }

        if ((!useKerberos) && (!Asemon_logger.disableENCPASS)) {
            props.put("ENCRYPT_PASSWORD","TRUE");
        
            // don't need next property if Certicom jars are in same dir as Jconn3.jar
            //props.put("JCE_PROVIDER_CLASS","com.certicom.ecc.jcae.Certicom");
        }
        
        props.put("CONNECTION_FAILOVER", "TRUE"); // Used by JNDI
                                                  // Note : when TRUE, try the next entry in ini or interfaces file
                                                  //        if any connection error including when login/password is incorrect

        if (charset!=null) props.put("CHARSET", charset);

        if (useKerberos) {
            props.put("REQUEST_KERBEROS_SESSION","TRUE");
            props.put("SERVICE_PRINCIPAL_NAME", srv);
            System.setProperty("javax.security.auth.useSubjectCredsOnly", "false");
            System.setProperty("java.security.auth.login.config","jaas.conf");
        }

        // Set the HOSTPROCESS ID
        props.put("HOSTPROC", Long.toString(Asemon_logger.osProcessId));

        if (Asemon_logger.enableJNDI)
            cnx = DriverManager.getConnection
                ("jdbc:sybase:jndi:file:"+sAdd.iFile+"?"+srv,  props);
        else
            cnx = DriverManager.getConnection
                ("jdbc:sybase:Tds:"+sAdd.host+":"+sAdd.port,  props);


                
        if ((db != null) && ( db.trim().length() != 0 )) {
            Statement stmt = cnx.createStatement();
            stmt.executeUpdate("use " + db);
            // Check if connected to the correct db
            ResultSet rs=stmt.executeQuery("select db_name()" );
            rs.next();
            String curr_db = rs.getString(1);
            stmt.close();
            if (!curr_db.equals(db) ) {
                // database is not correct, close this connection
                Asemon_logger.printmess ("ERROR connectSRV. Srv="+srv + " : not connected in '" +db + "'");
                cnx.close();
                cnx = null;
            }            
        }
        
    }
    catch (java.sql.SQLException ex){
        if (ex.getSQLState().equals("JZ00L") && displayErrors) {
            // Login failed
            Asemon_logger.printmess ("ERROR connectSRV (1). Srv="+srv
                    + " State= " +ex.getSQLState()
                    + " Err= " +ex.getErrorCode()
                    + " Msg=" + ex.getMessage());
            ex=ex.getNextException();
            Asemon_logger.printmess ("ERROR connectSRV (2). Srv="+srv
                    + " State= " +ex.getSQLState()
                    + " Err= " +ex.getErrorCode()
                    + " Msg=" + ex.getMessage());
            return null;
        }

        if (( verbose || (
                       (!ex.getSQLState().equals("JZ006"))
                    && (!ex.getSQLState().equals("JZ0C0"))
                    //&& (!ex.getSQLState().equals("JZ00L"))  // login locked
                    )
                ) && displayErrors)
                Asemon_logger.printmess ("ERROR connectSRV (1). Srv="+srv + " : " +ex);
        ex=ex.getNextException();
  	    while (ex!=null)	{
  	        if (displayErrors) Asemon_logger.printmess ("ERROR connectSRV (2). Srv="+srv + " : "+ ex);
            ex=ex.getNextException();
  	    }
  	    return null;
    }
    catch (Exception e) {
            Asemon_logger.printmess ("ERROR connectSRV (3). Srv="+srv + " : "+e);
  	    return null;
    }

    return cnx;
  }

  static int getConfigOption(Connection monSrvConn,String opt) {
    int optValue = -1;
    try {
      Statement stmt = monSrvConn.createStatement();
      ResultSet rs=stmt.executeQuery("select b.value from master..sysconfigures a, master..syscurconfigs b where a.config=b.config and upper(a.name) = upper('" +opt + "')" );
      rs.next();
      optValue = rs.getInt(1);
    }
    catch (Exception e) { 
      	Asemon_logger.printmess("ERROR getConfigOption - error : "+e);
        return -1;
    }
    return optValue;
  }

  static boolean connect_archive_SRV (ArchCnx aArchServCnx, boolean nowait) {
      aArchServCnx.archive_conn = null; // Force reopen of the connection if not completely cleared last time it was used
      boolean verbose=true;
      boolean displayErrors = true;
      if (Asemon_logger.archive_server!=null) {

          while (aArchServCnx.archive_conn == null)  {
              aArchServCnx.archive_conn = (SybConnection)CnxMgr.connectSRV(Asemon_logger.archive_user, Asemon_logger.archive_server, Asemon_logger.archive_base, Asemon_logger.archive_charset, "For archive server '"+Asemon_logger.archive_server+"', user '"+Asemon_logger.archive_user+"' - ", verbose, "asemon_arch", Asemon_logger.archive_useKerberos, displayErrors, Asemon_logger.archive_packet_size);
              if (aArchServCnx.archive_conn==null) {
                  if (nowait) return false;
                  if (displayErrors)
                      Asemon_logger.printmess("Trying to reconnect to archive server every 10 s ...");
                  try {
                    java.lang.Thread.sleep(10000);  // wait 10 s before retrying
                    displayErrors = false;
                    verbose=false;
                  }
                  catch (Exception e) {}
              }
          }

          try {
              Statement stmt = aArchServCnx.archive_conn.createStatement();
              ResultSet rs=stmt.executeQuery("select substring(@@version, 1, charindex('/', @@version)-1)");
              rs.next();
              String product= rs.getString(1);
              if (product.equalsIgnoreCase("SYBASE IQ"))
                  Asemon_logger.archive_DBMS = "IQ";
              else
                  Asemon_logger.archive_DBMS = "ASE";
              stmt.close();
          }
          catch (Exception e) {
              e.printStackTrace();
          }

          Asemon_logger.printmess ("Connected to archive server : "+ Asemon_logger.archive_server + " Database : " + Asemon_logger.archive_base);

          if (Asemon_logger.archive_DBMS.equalsIgnoreCase("IQ")) return true;
          
          try {
              Statement stmt = aArchServCnx.archive_conn.createStatement();
              int len = Asemon_logger.config_file.length();
              int start, end;
              if (len > 30) {start=len-30; end=len;}
              else {start=0; end=len-1;}
              stmt.executeUpdate("set clientapplname '"+Asemon_logger.config_file.substring(start, end)+"'");
              stmt.close();
          }
          catch (Exception e) {
              e.printStackTrace();
          }
      }
      return true;
  }

  static boolean connect_archive_SRV_asPurge (MonitoredSRV msrv, boolean printCnxMess, boolean nowait) {
      if (Asemon_logger.archive_server!=null) {
          msrv.purge_conn=null;
          boolean displayErrors=true;
          boolean verbose = printCnxMess;

          while (msrv.purge_conn==null) {
              msrv.purge_conn = CnxMgr.connectSRV(Asemon_logger.archive_user, Asemon_logger.archive_server, Asemon_logger.archive_base, Asemon_logger.archive_charset, "For archive server '"+Asemon_logger.archive_server+"', user '"+Asemon_logger.archive_user+"' - ", verbose, "asemon_purge", Asemon_logger.archive_useKerberos, displayErrors, Asemon_logger.archive_packet_size);
              if (msrv.purge_conn==null) {
                  if (nowait) return false;
                  if (displayErrors)
                      Asemon_logger.printmess("Trying to reconnect to archive server every 10 s ...");
                  try {
                    java.lang.Thread.sleep(10000);  // wait 10 s before retrying
                    displayErrors = false;
                    verbose=false;
                  }
                  catch (Exception e) {}
              }
          }


          if (printCnxMess)
              Asemon_logger.printmess ("Connected to archive server : "+ Asemon_logger.archive_server + " Database : " + Asemon_logger.archive_base);
          try {
              Statement stmt = msrv.purge_conn.createStatement();
              int len = Asemon_logger.config_file.length();
              int start, end;
              if (len > 30) {start=len-30; end=len;}
              else {start=0; end=len-1;}
              stmt.executeUpdate("set clientapplname '"+Asemon_logger.config_file.substring(start, end)+"'");
              stmt.close();
          }
          catch (Exception e) {
              e.printStackTrace();
          }
      }
      return true;
  }


  static boolean connectMonitoredRS (MonitoredSRV msrv, boolean verbose, boolean nowait) {
      Connection monSrvConn=null;
      Statement stmt = null;
      Connection RSSDConn=null;

      boolean displayErrors=true;
      while (monSrvConn == null)  {
          if (msrv.packet_size==0)
              // Packet size not set in config file, force to 2K
              msrv.packet_size = 2048;
          monSrvConn = connectSRV(msrv.user, msrv.name, null,msrv.charset, "For monitored server '"+msrv.name+"', user '"+msrv.user+"' - ", verbose, null, msrv.useKerberos, true, msrv.packet_size);
          if (monSrvConn==null) {
              if (nowait) return false;
              if (displayErrors)
                  Asemon_logger.printmess("Connection  to " +msrv.name +" is closed. Trying to reconnect every 10 s ...");
              try {
                java.lang.Thread.sleep(10000);  // wait 10 s before retrying
                displayErrors = false;
                verbose=false;
              }
              catch (Exception e) {}
          }
      }

      // Get RS version of monitered server
      try {
          stmt = monSrvConn.createStatement();
          ResultSet rs=stmt.executeQuery(
                "admin version");
          rs.next();
          StringBuffer versionStrB = new StringBuffer(rs.getString(1));
          versionStrB = new StringBuffer(versionStrB.substring(versionStrB.indexOf("/")+1,versionStrB.length()));
          String ver = new String(versionStrB.substring(0,versionStrB.indexOf("/")));
          ver = ver.replaceAll("\\.", "");
          if (ver.length()>4)
            ver=ver.substring(0,4);
          if (ver.length()==3)
            ver=ver+"0";
          msrv.version = Integer.parseInt(ver);
      }
      catch (Exception e) { 
      	Asemon_logger.printmess("ERROR connectMonitoredRS : error getting version. "+e);
        try {
              monSrvConn.close();
        }
        catch (Exception ex) {}
      	return false;
      }

      Asemon_logger.printmess ("connectMonitoredRS - connected to : " +msrv.name + " Version : "+ msrv.version);
      // save connection in the cnx structure
      msrv.monSrvConn = monSrvConn;
      msrv.needRSSDServer = false;

      if (msrv.version > 1500) {
          // Check statistics configuration
          try {
              String configuration, current;
              ResultSet rs=stmt.executeQuery(
                    "admin statistics, 'status'");
              while (rs.next()) {
                  configuration = rs.getString(1);
                  current = rs.getString(3);
                  if ((configuration.compareToIgnoreCase("stats_sampling")==0) && (current.compareToIgnoreCase("OFF")==0) ) {
                      Asemon_logger.printmess("WARNING connectMonitoredRS : stats_sampling = OFF. Not all statistics will be captured.");
                      Asemon_logger.printmess("You should execute \"configure replication server set stats_sampling to 'ON'\" on RS");
                  }
              }
              stmt.close();
          }
          catch (Exception e) {
            Asemon_logger.printmess("ERROR connectMonitoredRS : error getting statistics status. "+e);
            try {
                  monSrvConn.close();
            }
            catch (Exception ex) {}
            return false;
          }
          // Check if RSSD info is required and configured
          if (msrv.version<1550) {
              msrv.needRSSDServer = true;
              if ((msrv.RSSDUser == null) || (msrv.RSSDServer == null)){
                Asemon_logger.printmess("ERROR connectMonitoredRS : for RS15 < 15.5, RSSD info should be defined in config file");
                try {
                  monSrvConn.close();
                }
                catch (Exception ex) {}
                return false;
             }
          }
      }


      if (msrv.needRSSDServer) {
          // Connect to the RSSDServer
          RSSDConn = connectSRV(msrv.RSSDUser, msrv.RSSDServer, msrv.RSSDDatabase,msrv.charset, "For RSSD server, ", verbose, null, msrv.useKerberos, true, msrv.packet_size);
          if (RSSDConn==null) {
              // Close connection to RS
              try {
                  monSrvConn.close();
              } catch (Exception e) {}
              monSrvConn=null;
              return false;
          }
          // save connection in the cnx structure
          msrv.RSSDConn = RSSDConn;
      }

      return true;
  }  
  
  static boolean connectMonitoredRAO (MonitoredSRV msrv, boolean verbose, boolean nowait) {
      Connection monSrvConn=null;

      boolean displayErrors = true;
      while (monSrvConn == null)  {
          monSrvConn = connectSRV(msrv.user, msrv.name, null,msrv.charset, "For monitored server '"+msrv.name+"', user '"+msrv.user+"' - ", verbose, null, msrv.useKerberos, true, msrv.packet_size);
          if (monSrvConn==null) {
              if (nowait) return false;
              if (displayErrors)
                  Asemon_logger.printmess("Connection  to " +msrv.name +" is closed. Trying to reconnect every 10 s ...");
              try {
                java.lang.Thread.sleep(10000);  // wait 10 s before retrying
                displayErrors = false;
                verbose=false;
              }
              catch (Exception e) {}
          }
      }

      // Get RS version of monitered server
/*
      try {
          stmt = monSrvConn.createStatement();
          ResultSet rs=stmt.executeQuery(
                "admin version");
          rs.next();
          StringBuffer versionStrB = new StringBuffer(rs.getString(1));
          versionStrB = new StringBuffer(versionStrB.substring(versionStrB.indexOf("/")+1,versionStrB.length()));
          versionStrB = new StringBuffer(versionStrB.substring(0,versionStrB.indexOf("/")));
          versionStrB.deleteCharAt(versionStrB.indexOf("."));
          cnx.version = Integer.parseInt(versionStrB.toString());
      }
      catch (Exception e) { 
      	Asemon_logger.printmess("ERROR connectMonitoredRS : error getting version. "+e);
      	return false;
      }
*/
      Asemon_logger.printmess ("connectMonitoredRAO - connected to : " +msrv.name + " Version : "+ msrv.version);
      // save connection in the cnx structure
      msrv.monSrvConn = monSrvConn;
      return true;
  }  
  
  static boolean connectMonitoredIQ (MonitoredSRV msrv, boolean verbose, boolean nowait) {
      Connection monSrvConn=null;
      Statement stmt = null;
      
      boolean displayErrors = true;
      while (monSrvConn == null)  {
          if (msrv.packet_size==0)
              // Packet size not set in config file, force to 2K
              msrv.packet_size = 2048;
          monSrvConn = connectSRV(msrv.user, msrv.name, null,msrv.charset, "For monitored server '"+msrv.name+"', user '"+msrv.user+"' - ", verbose, null, msrv.useKerberos, true, msrv.packet_size);
          if (monSrvConn==null) {
              if (nowait) return false;
              if (displayErrors)
                  Asemon_logger.printmess("Connection  to " +msrv.name +" is closed. Trying to reconnect every 10 s ...");
              try {
                java.lang.Thread.sleep(10000);  // wait 10 s before retrying
                displayErrors = false;
                verbose=false;
              }
              catch (Exception e) {}
          }
      }

      try {
          stmt = monSrvConn.createStatement();
          ResultSet rs=stmt.executeQuery(
                "select substring("+
                   "substring(@@version,charindex ('/',@@version)+1, datalength(@@version)),"+
                   "1, "+
                   "charindex ('/',substring(@@version,charindex ('/',@@version)+1, datalength(@@version)))-1"+
                ")"
           );
          rs.next();
          msrv.versionStr = rs.getString(1);
      }
      catch (Exception e) { 
      	Asemon_logger.printmess("ERROR connectMonitoredIQ : error getting version. "+e);
        try {
              monSrvConn.close();
        }
        catch (Exception ex) {}
        return false;
      }

      // Force mode "chained=OFF" to avoid versionning
      try {
          stmt = monSrvConn.createStatement();
          stmt.executeUpdate("set temporary option chained='OFF'");
      }
      catch (Exception e) { 
      	Asemon_logger.printmess("ERROR connectMonitoredIQ : error setting CHAINED=OFF. "+e);
        try {
              monSrvConn.close();
        }
        catch (Exception ex) {}
      	return false;
      }


      Asemon_logger.printmess ("connectMonitoredIQ - connected to : " +msrv.name + " Version : "+ msrv.versionStr);
      // save connection in the cnx structure
      msrv.monSrvConn = monSrvConn;
      return true;
  }  
  
  
  static boolean connectMonitoredASE (MonitoredSRV msrv, String checkMonitoringConfig, boolean verbose, boolean nowait) {
      Connection monSrvConn=null;
      Statement stmt = null;
      
      boolean displayErrors = true;
      while (monSrvConn == null)  {
          monSrvConn = connectSRV(msrv.user, msrv.name, "tempdb", msrv.charset, "For monitored server '"+msrv.name+"', user '"+msrv.user+"' - ", verbose, "asemon_logger", msrv.useKerberos, true, msrv.packet_size);
          if (monSrvConn==null) {
              if (nowait) return false;
              if (displayErrors)
                  Asemon_logger.printmess("Connection  to " +msrv.name +" is closed. Trying to reconnect every 10 s ...");
              try {
                java.lang.Thread.sleep(10000);  // wait 10 s before retrying
                displayErrors = false;
                verbose=false;
              }
              catch (Exception e) {}
          }
      }

      // Get ASE version of monitored server, and current time
      try {
          stmt = monSrvConn.createStatement();
          
          ResultSet rs=stmt.executeQuery(                                                                        
                "select ts=getdate(), substring(" +
                       "substring(@@version,charindex('ise/',@@version)+4,datalength(@@version))," +
                       "1," +                                                                                    
                       "charindex('/',substring(@@version,charindex('ise/',@@version)+4,datalength(@@version)))-1" +
                   ")");                                                                                         
          rs.next();
          Timestamp ts = rs.getTimestamp(1);
          msrv.timeAdjust.adjustTime(ts);
          String s = rs.getString(2);
          s=s.replaceAll("\\.", "");                                                                             
          if (s.length()>4)
              s=s.substring(0,4);
          msrv.version = Integer.parseInt(s);
          if (msrv.version == 155)
              // special case for V15.5
              msrv.version= 1550;
                   
          // set clientapplname to srvDescriptor : this is usedConnections to distinguish several connection to same ASE for different purposes
          stmt.executeUpdate("set clientapplname " + msrv.srvDescriptor);
          
          // Check if asemon_logger is already connected (avoid automatic reconnection which can saturate connections on the server)
          rs=stmt.executeQuery(
                "select isnull(count(*),0) from master..sysprocesses where program_name='asemon_logger' and clientapplname='" + msrv.srvDescriptor +"'");
          rs.next();
          if (rs.getInt(1) > 1){
              Asemon_logger.printmess("ERROR : asemon_logger is already connected. Closing this connection.");
              monSrvConn.close();
              return false;
          }
          
      }
      catch (Exception e) { 
      	Asemon_logger.printmess ("ERROR connectMonitoredASE : error getting version. "+e);
        try {
              monSrvConn.close();
        }
        catch (Exception ex) {}
      	return false;
      }

      Asemon_logger.printmess ("connectMonitoredASE - connected to : " +msrv.name + " Version : "+ msrv.version);
      Asemon_logger.printmess ("Time difference (ms) between ASE and asemon_logger (positive when ASE is in advance) : " + msrv.timeAdjust.value() );
      
      if ((checkMonitoringConfig!=null) && (checkMonitoringConfig.equals("yes"))) {
          // Check if user has "mon_role"
          try {
              stmt = monSrvConn.createStatement();
              ResultSet rs=stmt.executeQuery(
                    "select proc_role('mon_role')");
              rs.next();
              msrv.mon_role = rs.getInt(1);
          }
          catch (Exception e) { 
              Asemon_logger.printmess("ERROR connectMonitoredASE : error getting mon_role. "+e);
                try {
                      monSrvConn.close();
                }
                catch (Exception ex) {}
              return false;
          }
          if (msrv.mon_role != 1) {
              Asemon_logger.printmess ("connectMonitoredASE : 'mon_role' missing for user '"+msrv.user+"'");
                try {
                      monSrvConn.close();
                }
                catch (Exception ex) {}
              return false;
          }

          try {
              // Check if user has "sa_role"
              stmt = monSrvConn.createStatement();
              ResultSet rs=stmt.executeQuery(
                    "select proc_role('sa_role')");
              rs.next();
              msrv.sa_role = rs.getInt(1);

              msrv.asemon_indirect_sa_role =0;
              if (msrv.sa_role == 0) {
                  // Check if user has "asemon_indirect_sa_role"
                  stmt = monSrvConn.createStatement();
                  rs=stmt.executeQuery("sp_asemon_check_sa_role");
                  rs.next();
                  msrv.asemon_indirect_sa_role = rs.getInt(1);
              }
          }
          catch (Exception e) { 
          }


          // Special actions when user has no sa_role
          if ( (msrv.sa_role == 0 ) && (msrv.asemon_indirect_sa_role == 0)) {
              Asemon_logger.printmess ("WARNING : Login '"+msrv.user+ "' has no sa_role or asemon_indirect_sa_role");
              msrv.disableRAmonitor=true;
          }

          if ( msrv.sa_role == 0 ) {
              // User has no sa_role
              // check if user is defined in all databases

              // Use a temporary table rather than a cursor to avoid maintaining shared locks on rows of sysdatabases table
              try {
                  stmt = monSrvConn.createStatement();
                  Statement stmt2 = monSrvConn.createStatement();
                  String dbname;
                  ResultSet rs = stmt.executeQuery(
                        "select name from master..sysdatabases where name != 'sybsecurity' and status&256!=256 and status2&16!=16 and status2&32!=32");
                  while (rs.next()){
                      dbname = rs.getString(1);
                      try {
                          stmt2.executeQuery("select 1 from "+dbname+"..sysobjects where id=1");
                      }
                      catch (SQLException  sqlex) {
                          if (sqlex.getErrorCode()==10351)
                              Asemon_logger.printmess ("ERROR connectMonitoredASE : user '"+msrv.user+"' is not defined in database '"+dbname+"'");
                          else throw sqlex;
                      }
                  }
                  
              }
              catch (SQLException  sqlex) {
                     Asemon_logger.printmess ("ERROR connectMonitoredASE : " +sqlex.getErrorCode() + " "+ sqlex.getMessage());
                        try {
                              monSrvConn.close();
                        }
                        catch (Exception ex) {}
                     return false;
              }
              catch (Exception e) { 
                     Asemon_logger.printmess ("ERROR connectMonitoredASE : error verifying access to all db's. "+e);
                        try {
                              monSrvConn.close();
                        }
                        catch (Exception ex) {}
                     return false;
              }

              if (!Asemon_logger.skipSAProcs) {
                  // Check version of installed asemon procs
                  int asemon_procs_version;
                  try {
                      ResultSet rs=stmt.executeQuery("sp_asemon_procs_version");
                      rs.next();
                      asemon_procs_version = rs.getInt(1);
                  }
                  catch (SQLException sqlex) {
                      if (sqlex.getErrorCode()==2812) {
                          Asemon_logger.printmess("ERROR : asemon stored procs not installed. Run script 'install_asemon_procs_non_sa_Vxxx' (where xxx depends on your monitored ASE version)");
                      }
                      else if (sqlex.getErrorCode()==10330) {
                          Asemon_logger.printmess("ERROR : no exec rigths on asemon stored procs");
                      }
                      else
                          Asemon_logger.printmess("ERROR checking asemon stored procs : "+sqlex.getErrorCode()+" "+sqlex.getMessage());
                        try {
                              monSrvConn.close();
                        }
                        catch (Exception ex) {}
                        try {
                              monSrvConn.close();
                        }
                        catch (Exception ex) {}
                      return false;
                  }
                  if (asemon_procs_version!=Asemon_logger.asemon_procs_version) {
                      Asemon_logger.printmess("ERROR. Reinstall asemon stored procs (current version : "+asemon_procs_version+" Should be : "+Asemon_logger.asemon_procs_version);
                        try {
                              monSrvConn.close();
                        }
                        catch (Exception ex) {}
                      return false;
                  }
                  Asemon_logger.printmess(msrv.user+" has asemon_indirect_sa_role. Will use asemon procs");
              }
          }



          // Check configured options
        if ( getConfigOption(monSrvConn,"enable monitoring") != 1) {
            Asemon_logger.printmess ("WARNING : Option 'enable monitoring' is set to 0");
            /*
            Asemon_logger.printmess ("ERROR : You should configure server option 'enable monitoring' to 1");
            try {
                  monSrvConn.close();
            }
            catch (Exception ex) {}
            return false;
             */
        }    

        if ( getConfigOption(monSrvConn,"max SQL text monitored") <= 0) {
            Asemon_logger.printmess ("WARNING : You should configure server option 'max SQL text monitored' to a value > 0 (ex. 512)");
            msrv.maxSQLtextMonitored = false;
        }

        msrv.sql_batch_capture=true;
        if ( getConfigOption(monSrvConn,"SQL batch capture") != 1) {
            Asemon_logger.printmess ("WARNING : Server option 'SQL batch capture' is not set, not all statistics will be captured");
            msrv.sql_batch_capture=false;
        }    

        if ( getConfigOption(monSrvConn,"object lockwait timing") != 1) {
            Asemon_logger.printmess ("WARNING : Server option 'Object lockwait timing' is not set, not all statistics will be captured");
            msrv.object_lockwait_timing=false;
        }    

        msrv.per_object_stats = true;
        if ( getConfigOption(monSrvConn,"per object statistics active") != 1) {
            Asemon_logger.printmess ("WARNING : Server option 'per object statistics active' is not set, not all statistics will be captured");
            msrv.per_object_stats = false;
        }    


        msrv.statement_stats = true;
        if ( getConfigOption(monSrvConn,"statement statistics active") != 1) {
            Asemon_logger.printmess ("WARNING : Server option 'statement statistics active' is not set, not all statistics will be captured");
            msrv.statement_stats = false;
        }    

        if ( getConfigOption(monSrvConn,"wait event timing") != 1) {
            Asemon_logger.printmess ("WARNING : Option 'wait event timing' is set to 0");
            /*
            Asemon_logger.printmess ("ERROR : You should configure server option 'wait event timing' to 1");
            try {
                  monSrvConn.close();
            }
            catch (Exception ex) {}
            return false;
             */
        }    

        if ( getConfigOption(monSrvConn,"statement pipe active") == 1) {
            msrv.statementPipeActive = true;
        }

        if ( getConfigOption(monSrvConn,"sql text pipe active") == 1) {
            msrv.sqlTextPipeActive = true;
        }

        if ( getConfigOption(monSrvConn,"deadlock pipe active") == 1) {
            msrv.deadlockPipeActive = true;
        }
    }  // end if checkMonitoredConfig
      
    // save connection in the cnx structure
    msrv.monSrvConn = monSrvConn;
    return true;
  }
    
    
    
}
