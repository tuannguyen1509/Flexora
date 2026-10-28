<?php
define( 'ABSPATH', __DIR__ );
function add_action() {}
require dirname( __DIR__ ) . '/inc/core/html-minify.php';
function verify_html( $input, $expected, $label ) {
    if ( az_minify_html_output( $input ) !== $expected ) { fwrite( STDERR, "FAIL $label\n" ); exit( 1 ); }
    echo "PASS $label\n";
}
verify_html( "<html>\n   <body>\n <p>Hello</p>\n </body>\n</html>", '<html> <body> <p>Hello</p> </body> </html>', 'inter-tag indentation reduced' );
verify_html( "<html><body><span>A</span>\n <span>B</span></body></html>", '<html><body><span>A</span> <span>B</span></body></html>', 'inline word separator preserved' );
foreach ( array( 'script', 'style', 'pre', 'code', 'textarea', 'svg', 'math' ) as $tag ) {
    $input = "<html><body><$tag>\n A  B\n <x>\n </x></$tag></body></html>";
    verify_html( $input, $input, "$tag region preserved" );
}
$comment = "<html><!-- wp:paragraph\n metadata --><body></body></html>";
verify_html( $comment, $comment, 'comments preserved' );
verify_html( '{"message":"unchanged"}', '{"message":"unchanged"}', 'JSON untouched' );
