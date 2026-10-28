<?php
/** Read-only .wpress index inspection; optional extraction into a NEW private directory. */
if ( PHP_SAPI !== 'cli' || count( $argv ) < 3 ) { exit( "Usage: php inspect-wpress.php archive output-directory [--extract]\n" ); }
$input = fopen( $argv[1], 'rb' );
$output = $argv[2];
$extract = in_array( '--extract', $argv, true );
if ( ! is_dir( $output ) ) { mkdir( $output, 0755, true ); }
$base = realpath( $output );
$count = 0; $bytes = 0; $themes = array(); $plugins = array(); $metadata = array();
while ( ! feof( $input ) ) {
	$header = fread( $input, 4377 );
	if ( '' === $header ) { break; }
	if ( strlen( $header ) !== 4377 ) { throw new RuntimeException( 'Truncated header' ); }
	$item = unpack( 'a255name/a14size/a12mtime/a4088path/a8crc', $header );
	$name = trim( $item['name'], "\0 \r\n\t" );
	if ( '' === $name ) { break; }
	$size = trim( $item['size'], "\0 " );
	if ( ! ctype_digit( $size ) ) { throw new RuntimeException( 'Invalid size' ); }
	$size = (int) $size;
	$path = trim( $item['path'], "\0 \r\n\t" );
	$relative = ( '.' === $path || '' === $path ? '' : $path . '/' ) . $name;
	if ( preg_match( '#(^/|^[A-Za-z]:|\\\\|(^|/)\.\.(/|$)|\x00)#', $relative ) ) { throw new RuntimeException( 'Unsafe archive path' ); }
	if ( ftell( $input ) + $size > filesize( $argv[1] ) ) { throw new RuntimeException( 'Entry exceeds archive' ); }
	if ( preg_match( '#^themes/([^/]+)/#', $relative, $m ) ) { $themes[ $m[1] ] = true; }
	if ( preg_match( '#^plugins/([^/]+)/#', $relative, $m ) ) { $plugins[ $m[1] ] = true; }
	if ( 'package.json' === $relative ) {
		$package = json_decode( fread( $input, $size ), true );
		foreach ( array( 'WordPress','WordPressVersion','PHP','PHPVersion','Template','Stylesheet','SiteURL','HomeURL','Database','DatabasePrefix' ) as $key ) {
			if ( isset( $package[ $key ] ) && is_scalar( $package[ $key ] ) ) { $metadata[ $key ] = $package[ $key ]; }
		}
		fseek( $input, -$size, SEEK_CUR );
	}
	if ( $extract ) {
		$directory = preg_match( '#^(themes|plugins|uploads|mu-plugins|languages)/#', $relative ) || in_array( $relative, array( 'index.php','advanced-cache.php','object-cache.php','db.php','sunrise.php' ), true ) ? 'content' : 'archive-meta';
		$target = $base . DIRECTORY_SEPARATOR . $directory . DIRECTORY_SEPARATOR . str_replace( '/', DIRECTORY_SEPARATOR, $relative );
		if ( file_exists( $target ) ) { throw new RuntimeException( 'Refusing to overwrite extracted file' ); }
		if ( ! is_dir( dirname( $target ) ) ) { mkdir( dirname( $target ), 0755, true ); }
		$handle = fopen( $target, 'xb' );
		$crc = hash_init( 'crc32b' );
		$remaining = $size;
		while ( $remaining > 0 ) {
			$chunk = fread( $input, min( 1048576, $remaining ) );
			if ( '' === $chunk ) { throw new RuntimeException( 'Truncated data' ); }
			hash_update( $crc, $chunk );
			fwrite( $handle, $chunk ); $remaining -= strlen( $chunk );
		}
		fclose( $handle );
		$expected = trim( $item['crc'], "\0 " );
		$actual = hash_final( $crc );
		if ( '' !== $expected && strtolower( $expected ) !== $actual ) { throw new RuntimeException( 'CRC mismatch: ' . $relative ); }
	} else { fseek( $input, $size, SEEK_CUR ); }
	$count++; $bytes += $size;
}
fclose( $input );
$result = array( 'files' => $count, 'payload_bytes' => $bytes, 'themes' => array_keys( $themes ), 'plugins' => array_keys( $plugins ), 'metadata' => $metadata, 'extracted' => $extract );
file_put_contents( $base . '/inventory.json', json_encode( $result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );
echo json_encode( $result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ), "\n";
