<?php
    require 'version.php';

    if ( isset( $argv ) ) {
        $jqm = isset( $argv[ 1 ] ) ? $argv[ 1 ] : '';
        $jq = isset( $argv[ 2 ] ) ? $argv[ 2 ] : '';
        $jqm_path ="http://code.jquery.com/mobile/" . $jqm;
        $jq_path ="http://code.jquery.com/jquery-" . $jq . ".min.js";

        chdir( 'jqm' );
        rrmdir( $jqm );
        
        //get most recent version of jqm
        $versions = preg_grep('/^([^.])/', scandir( "." ));
        $most_recent = '';
        foreach( $versions as $version ) {
            if ( versionCompare( $version, $most_recent ) ) {
                $most_recent = $version;
            }
        }
        
        mkdir( $jqm );
        chdir( $jqm );
                
        //Get jQuery JS
        echo "Getting jQuery JS...\n";
        getFile( $jq_path, "jquery.min.js" );
        //Get jQuery Mobile Images
        echo "Getting jQuery Mobile Images...\n";
        mkdir( 'images' );
        chdir( 'images' );
        getFile( $jqm_path . "/images/icons-18-white.png", "icons-18-white.png" );
        getFile( $jqm_path . "/images/icons-18-black.png", "icons-18-black.png" );
        getFile( $jqm_path . "/images/icons-36-white.png", "icons-36-white.png" );
        getFile( $jqm_path . "/images/icons-36-black.png", "icons-36-black.png" );
        getFile( $jqm_path . "/images/ajax-loader.gif", "ajax-loader.gif" );
        chdir( '..' );
        //Get jQuery Mobile JS
        echo "Getting jQuery Mobile JS...\n";
        getFile( $jqm_path . "/jquery.mobile-" . $jqm . ".min.js", "jqm.min.js" );
        //Get jQuery Mobile structure
        echo "Getting jQuery Mobile structure CSS...\n";
        getFile( $jqm_path . "/jquery.mobile.structure-" . $jqm . ".css", "jqm.structure.css" );
        //Get jQuery Mobile default
        echo "Getting jQuery Mobile default theme CSS...\n";
        getFile( $jqm_path . "/jquery.mobile.theme-" . $jqm . ".css", "jqm.default.theme.css" );
        //Create TR starter theme
        echo "Generating ThemeRoller starter theme CSS...\n";
        @createStarter( "jqm.default.theme.css", "jqm.starter.theme.css" );
        //Copy preview.html from most recent version present
        echo "Generating Preview Markup...\n";
        $contents = @file_get_contents( '../' . $most_recent . '/preview.html' );
		writeFile( 'preview.html', $contents );
		//Create user_themes directory with README placeholder
        echo "Creating user_themes directory...\n";
        mkdir( "user_themes" );
		writeFile( 'user_themes/README.md', 'This is where theme files are temporarily stored when a user "shares" a theme.' );
		//Create empty panel.js file
		writeFile( 'panel.js', '' );
		//Altering version.php with new key value pair for jQm, and jQuery versions
		chdir( '../..' );
		$contents = file_get_contents( 'version.php' );
		$contents = preg_replace( "/(\\\$ALL_JQUERY_VERSIONS.*)\n.*\);/s", "$1,\n\t\"" . $jqm . "\" => \"" . $jq . "\"\n);", $contents );
		writeFile( 'version.php', $contents );
        
    } else {
        echo 'This script must be executed via command line';
    }

    function check404( $url ) {
        return "404" == exec( "curl --write-out %{http_code} --silent --output /dev/null " . $url );
    }

    function getFile( $url, $filename ) {
        if ( check404( $url ) ) {
            echo $url . " could not be found. Generating empty file for " . $filename . "\n";
    		writeFile( $filename, '' );
    		return false;
    	} else {
    	    //get contents
    		$handle = curl_init( $url );
    		curl_setopt($handle, CURLOPT_RETURNTRANSFER, 1);
    		$contents = curl_exec( $handle );
    		curl_close( $handle );
		
    		//write them out to local file
    		writeFile( $filename, $contents );
    		return true;
    	}
    }
    
    function writeFile( $filename, $string ) {
        $file = fopen( $filename, 'w' );
        fwrite( $file, $string );
        fclose( $file );
    }
    
    function createStarter( $default, $starter ) {
        $contents = file_get_contents( $default );
        
        $contents = preg_replace( "/\/\*\s*A\s*-*\*\/.*(\/\*\s*B\s*-*\*\/)/s", "$1", $contents );
        $contents = preg_replace( "/\/\*\s*B\s*-*\*\/.*(\/\*\s*C\s*-*\*\/)/s", "$1", $contents );
        $contents = preg_replace( "/\/\*\s*D\s*-*\*\/.*(\/\*\s*E\s*-*\*\/)/s", "$1", $contents );
        $contents = preg_replace( "/\/\*\s*E\s*-*\*\/.*(\/\*\s*Structure\s*-*\*\/)/s", "$1", $contents );
        preg_match( "/(\/\*\s*C\s*-*\*\/.*)\/\*\sStructure\s\*\//s", $contents, $matches );
        $c_swatch = $matches[ 1 ];
        
        //Build A swatch
        $temp = preg_replace( "/-c\s/", "-a ", $c_swatch );
        $temp = preg_replace( "/-c,/", "-a,", $temp );
        $temp = preg_replace( "/{c-/", "{a-", $temp );
        $temp = preg_replace( "/-c:/", "-a:", $temp );
        $a_swatch = preg_replace( "/\/\*\s*C(\s*-*\*\/)/", "/* A$1", $temp );
        
        $contents = preg_replace( "/\/\*\s*C\s*-*\*\/.*\/\*\sStructure\s\*\//s",
            $a_swatch . "/* Structure */", $contents );
        
		writeFile( $starter, $contents );
    }

    function rrmdir($dir) {
        foreach(glob($dir . '/*') as $file) {
            if(is_dir($file))
                @rrmdir($file);
            else
                @unlink($file);
        }
        @rmdir($dir);
    }

?>