<?php

unset( $_SESSION['admin'] );
header('Location: '.F::route( 'admin/login' ));