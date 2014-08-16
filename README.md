### TEST:

```php
    <?php
        //get class
        $class = $GLOBALS[ 'hwp_dr_locate' ];

        //get test location
        $lat_long = $GLOBALS[ 'test_lat_long' ];

        //uncomment this for reality-test
        //$lat_long = array( 'lat' => '0', 'long' => '0' );

        //set distance
        $distance = 10;

        print_r ( $class->get_locations( $lat_long, $distance ));
```

### Add A Location Pod

```php
    <?php
        hwp_dr_add_location_pod( );
``
