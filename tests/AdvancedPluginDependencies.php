<?php
/**
 * Test Advanced_Plugin_Dependencies class.
 *
 * @package Advanced_Plugin_Dependencies
 *
 */
class Tests_Admin_WpPluginDependencies extends WP_UnitTestCase {
	/**
	 * Makes a class property accessible.
	 *
	 * @param object|string $obj_or_class The object or class.
	 * @param string        $prop         The property.
	 * @return ReflectionProperty The accessible property.
	 */
	private function make_prop_accessible( $obj_or_class, $prop ) {
		$property = new ReflectionProperty( $obj_or_class, $prop );
		$property->setAccessible( true );
		return $property;
	}

	/**
	 * Makes a class method accessible.
	 *
	 * @param object|string $obj_or_class The object or class.
	 * @param string        $method     The class method.
	 * @return ReflectionMethod The accessible method.
	 */
	private function make_method_accessible( $obj_or_class, $method ) {
		$method = new ReflectionMethod( $obj_or_class, $method );
		$method->setAccessible( true );
		return $method;
	}

	/**
	 * Tests that dependency slugs are returned correctly.
	 *
	 * @covers WP_Plugin_Dependencies_2::split_slug
	 *
	 * @dataProvider data_split_slug_should_return_correct_slug
	 *
	 * @param string $slug     A slug string.
	 * @param array  $expected A string of expected slug results.
	 */
	public function test_split_slug_should_return_correct_slug( $slug, $expected ) {
		$this->markTestSkipped( 'must be revisited.' );

		$advanced_dependencies = new Advanced_Plugin_Dependencies();
		$split_slug            = $this->make_method_accessible( $advanced_dependencies, 'split_slug' );

		// The slug is trimmed before being passed to the 'wp_plugin_dependencies_slug' filter.
		$actual = $split_slug->invoke( $advanced_dependencies, trim( $slug ) );
		$this->assertSame( $expected, $actual );
	}

	/**
	 * Data provider.
	 *
	 * @return array[]
	 */
	public function data_split_slug_should_return_correct_slug() {
		return array(
			'no slug, an endpoint, and one pipe at the start' => array(
				'slug'     => '|endpoint',
				'expected' => '|endpoint',
			),
			'no slug, an endpoint, and two pipes at the start' => array(
				'slug'     => '||endpoint',
				'expected' => '||endpoint',
			),
			'a slug, an endpoint, and one pipe in the middle' => array(
				'slug'     => 'slug|endpoint',
				'expected' => 'slug',
			),
			'a slug, an endpoint, and two pipes in the middle' => array(
				'slug'     => 'slug||endpoint',
				'expected' => 'slug||endpoint',
			),
			'a slug, no endpoint, and one pipe at the end' => array(
				'slug'     => 'slug|',
				'expected' => 'slug|',
			),
			'a slug, no endpoint, and two pipes at the end' => array(
				'slug'     => 'slug||',
				'expected' => 'slug||',
			),
			'a slug, no endpoint, and one pipe at the start and end' => array(
				'slug'     => '|slug|',
				'expected' => '|slug|',
			),
			'a slug, no endpoint, and two pipes at the start and end' => array(
				'slug'     => '||slug||',
				'expected' => '||slug||',
			),
			'a slug, an endpoint, and two pipes in the middle' => array(
				'slug'     => 'slug||endpoint',
				'expected' => 'slug||endpoint',
			),
			'a slug, an endpoint, and one pipe at the start, in the middle, and at the end' => array(
				'slug'     => '|slug|endpoint|',
				'expected' => '|slug|endpoint|',
			),
			'a slug, an endpoint, and one pipe at the start and end, and two pipes in the middle' => array(
				'slug'     => '|slug||endpoint|',
				'expected' => '|slug||endpoint|',
			),
			'a slug, an endpoint, and two pipes at the start and end, and one pipe in the middle' => array(
				'slug'     => '||slug|endpoint||',
				'expected' => '||slug|endpoint||',
			),
			'a slug, an endpoint, and two pipes at the start and end, and two pipes in the middle' => array(
				'slug'     => '||slug||endpoint||',
				'expected' => '||slug||endpoint||',
			),
			'a slug, an endpoint, and one pipe at the start and in the middle' => array(
				'slug'     => '|slug|endpoint',
				'expected' => '|slug|endpoint',
			),
			'a slug, an endpoint, and one pipe in the middle and at the end' => array(
				'slug'     => 'slug|endpoint|',
				'expected' => 'slug|endpoint|',
			),
			'a slug, an endpoint, and two spaces and a pipe at the start, and a pipe in the middle' => array(
				'slug'     => '  |slug|endpoint',
				'expected' => '|slug|endpoint',
			),
			'a slug, an endpoint, and two spaces before a pipe in the middle' => array(
				'slug'     => 'slug  |endpoint',
				'expected' => 'slug',
			),
			'a slug, an endpoint, and two spaces after a pipe in the middle' => array(
				'slug'     => 'slug|  endpoint',
				'expected' => 'slug',
			),
			'a slug, an endpoint, and a pipe in the middle, a pipe at the end, and two spaces at the end' => array(
				'slug'     => 'slug|endpoint|  ',
				'expected' => 'slug|endpoint|',
			),
			'a slug, an endpoint, and spaces pipe at front pipe in middle' => array(
				'slug'     => '     |slug|endpoint',
				'expected' => '|slug|endpoint',
			),
			'no slug, no endpoint, and one pipe'           => array(
				'slug'     => '|',
				'expected' => '|',
			),
			'no slug, no endpoint, and two pipes'          => array(
				'slug'     => '||',
				'expected' => '||',
			),
			'an empty slug'                                => array(
				'slug'     => '',
				'expected' => '',
			),
		);
	}
}
