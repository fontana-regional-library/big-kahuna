<?php
class Fontana_Events_API extends Fontana_Public {
/**
 * 
 * ADDING custom endpoint & rest fields to query 
 * tribe-events by service & location: 
 * 			
 *			http://fontana.local/wp-json/wp/v2/events?search=baskets
 *
 * In addition to filtering by location (term id) and services (term id), adds support to filter/search by:
 *			search = query (searches title & description)
 * 			organizer = Organizer ID
 * 			venue = Venue ID
 * 			start_date = Y-m-d (2018-11-06)
 * 
 */
	
	// Register custom endpoint for "tribe_events" post-type
	function events_api( $args, $post_type ) {
		if ( 'tribe_events' === $post_type ) {
				$args['show_in_rest'] = true;
				// customize the rest_base
				$args['rest_base']             = 'events';
		}
		return $args;
	}

	// Register fields to display in the REST API
	public function register_api_fields() {

		// Add "Start Date" post-meta to custom tribe events feed
		register_rest_field( array('tribe_events'), 'start_date',
			array(
				'get_callback'    => array( $this, 'get_start_date'),
				'update_callback' => null,
				'schema'          => null,
			)
		);
		
		// Add "End Date" post-meta to custom tribe events feed
		register_rest_field( array('tribe_events'), 'end_date',
			array(
				'get_callback'    => array( $this, 'get_end_date'),
				'update_callback' => null,
				'schema'          => null,
			)
		);
		
		// Add "Venue" post-meta / related post to custom tribe events feed
		register_rest_field( array('tribe_events'), 'venue',
			array(
				'get_callback'    => array( $this, 'get_venue'),
				'update_callback' => null,
				'schema'          => null,
			)
		);
			// Add "Organizer" post-meta / related post to custom tribe events feed
			register_rest_field( array('tribe_events'), 'organizer',
			array(
				'get_callback'    => array( $this, 'get_organizer'),
				'update_callback' => null,
				'schema'          => null,
			)
		);
	}
	
	/**
	 * 
	 * Sort tribe_events by start date, pre-filter to upcoming events,
	 * 
	 * https://developer.wordpress.org/reference/hooks/rest_this-post_type_query/
	 * 
	 */
	function events_api_upcoming($args, $request) {
		$today = the_time('Y-m-d G:i:00');
		$meta_query = array();
		$eventStartQuery = array(
			'key'	=>	'_EventStartDate',
			'value' => $today,
			'compare' => '>='
		);
		$args['orderby'] = 'meta_value_datetime';
		$args['order'] = 'ASC';
		$args['meta_key'] = '_EventStartDate';

    if($request['start_date']) {
			$eventStartQuery = array(
				'key'	=>	'_EventStartDate',
				'value' => $request['start_date'],
				'compare' => '>=',
				'type' => 'DATE'
				);
		}

		$meta_query[]=$eventStartQuery;

		if($request['venue']) {
			$eventVenueQuery = array(
				'key'	=>	'_EventVenueID',
				'value' => $request['venue'],
				'compare' => '=',
				'type' => 'integer'
				);
			$meta_query[]=$eventVenueQuery;
		}

		if($request['organizer']) {
			$eventOrganizerQuery = array(
				'key'	=>	'_EventOrganizerID',
				'value' => $request['organizer'],
				'compare' => '=',
				'type' => 'integer'
				);
			$meta_query[]=$eventOrganizerQuery;
		}

		$args['meta_query'] = $meta_query;
		return $args;
	}
  function events_api_response($data, $post, $context) {
		//$method = $request->get_method();

			//flatten out nested objects
			$data->data['title'] = $data->data['title']['rendered'];
			$data->data['content'] = $data->data['content']['rendered'];
      $data->data['excerpt'] = $data->data['excerpt']['rendered'];
      $data->data['type'] = 'event';
		
			$imageId = get_post_meta( $post->ID, '_thumbnail_id', true );

			$data->data['image'] = $this->get_image_data($imageId);

			// add start_date_details
			$start = $data->data['start_date'];
			$data->data['start_date_details'] = $this->get_date_details($start);

			// add end_date_details
			$end = $data->data['end_date'];
			$data->data['end_date_details'] = $this->get_date_details($end);

			//filter out unneeded stuff
			//unset($data->data['author']);
			unset($data->data['link']);
			unset($data->data['modified']);
			unset($data->data['modified_gmt']);
			//unset($data->data['slug']);
			unset($data->data['status']);
			//unset($data->data['type']);
			unset($data->data['categories']);
			unset($data->data['_links']);
			//unset($data->data['author']);
			unset($data->data['featured_media']);
			unset($data->data['comment_status']);
			unset($data->data['ping_status']);
			unset($data->data['sticky']);
			unset($data->data['template']);
			unset($data->data['format']);
			unset($data->data['meta']);
			unset($data->data['featured_image']);
			$data->remove_link( 'collection' );
			$data->remove_link( 'self' );
			$data->remove_link( 'about' );
			$data->remove_link( 'author' );
			$data->remove_link( 'replies' );
			$data->remove_link( 'version-history' );
			$data->remove_link( 'https://api.w.org/featuredmedia' );
			$data->remove_link( 'https://api.w.org/attachment' );
			$data->remove_link( 'https://api.w.org/term' );
			$data->remove_link( 'curies' );
						
		return $data; 
	}

	// Build image array
	function get_image_data( $image_id ){
		$full_url = wp_get_attachment_image_url( $image_id, 'full' );
		$file     = get_attached_file( $image_id );

		$data = array(
			'url'       => $full_url,
			'id'        => $image_id,
			'extension' => pathinfo( $file, PATHINFO_EXTENSION ),
		);

		$metadata = wp_get_attachment_metadata( $image_id );

		if (
			false !== $metadata
			&& isset( $metadata['image_meta'], $metadata['file'], $metadata['sizes'] )
		) {
			unset( $metadata['image_meta'], $metadata['file'] );

			foreach ( $metadata['sizes'] as $size => &$meta ) {
				$size_image_src = wp_get_attachment_image_src( $image_id, $size );
				$meta['url']    = ! empty( $size_image_src[0] ) ? $size_image_src[0] : '';
				unset( $meta['file'] );
			}
			unset( $meta );

			$data = array_filter( array_merge( $data, $metadata ) );
		}

		return $data;
	}
	
	// Get Date Details
	function get_date_details( $date ) {
		if ( empty( $date ) ) {
			return array(
				'year'    => '',
				'month'   => '',
				'day'     => '',
				'hour'    => '',
				'minutes' => '',
				'seconds' => '',
			);
		}

		$time = strtotime( $date );

		return array(
			'year'    => date( 'Y', $time ),
			'month'   => date( 'm', $time ),
			'day'     => date( 'd', $time ),
			'hour'    => date( 'H', $time ),
			'minutes' => date( 'i', $time ),
			'seconds' => date( 's', $time ),
		);
	}

	// Callback: Get Start Date meta for tribe_events
	function get_start_date(){
		$id = get_the_ID();
		$startDate = get_post_meta($id, '_EventStartDate', true);
		return $startDate;
	}
	
	// Callback: Get End Date meta for tribe_events
	function get_end_date(){
		$id = get_the_ID();
		$endDate = get_post_meta($id, '_EventEndDate', true);
		return $endDate;
	}	
	
	// Callback: Get Venue Data for tribe_events
	function get_venue(){
		$venue = new stdClass;
		$id = get_the_ID();
		$venueId = get_post_meta($id, '_EventVenueID', true);
		$venuePost = get_post($venueId);
		$venueMeta = get_post_meta($venueId);
		$venue->id = $venuePost->ID;
		$venue->url = $venuePost->guid;
		$venue->venue = $venuePost->post_title;
		$venue->slug = $venuePost->post_name;
		$venue->address = $venueMeta['_VenueAddress'][0];
		$venue->city = $venueMeta['_VenueCity'][0];
		$venue->country = $venueMeta['_VenueCountry'][0];
		$venue->state = $venueMeta['_VenueState'][0];
		$venue->zip = $venueMeta['_VenueZip'][0];
		$venue->phone = $venueMeta['_VenuePhone'][0];
		$venue->website = $venueMeta['_VenueURL'][0];
		$venue->json_ld = new stdClass;
			$venue->json_ld->{'@type'} = "Place";
			$venue->json_ld->name = $venuePost->post_title;
			$venue->json_ld->description = $venuePost->post_content;
			$venue->json_ld->url = '';
			$venue->json_ld->address = new stdClass;
				$venue->json_ld->address->{'@type'} = "PostalAddress";
				$venue->json_ld->address->streetAddress = $venueMeta['_VenueAddress'][0];
				$venue->json_ld->address->addressLocality = $venueMeta['_VenueCity'][0];
				$venue->json_ld->address->addressRegion = $venueMeta['_VenueState'][0];
				$venue->json_ld->address->postalCode = $venueMeta['_VenueZip'][0];
				$venue->json_ld->address->addressCountry = $venueMeta['_VenueCountry'][0];
			$venue->json_ld->telephone = $venueMeta['_VenuePhone'][0];
			$venue->json_ld->sameAs = $venueMeta['_VenueURL'][0];
		return $venue;
	}

	// Callback: Get Organizer Data for tribe_events
	function get_organizer(){
		$organizers= array();
		$id = get_the_ID();
		$organizerIds = get_post_meta($id, '_EventOrganizerID', false);
		foreach($organizerIds as $organizerId) {
			$organizer = new stdClass;
			$organizerPost = get_post($organizerId);
			$organizerMeta = get_post_meta($organizerId);
			$organizer->id = $organizerPost->ID;
			$organizer->url = $organizerPost->guid;
			$organizer->organizer = $organizerPost->post_title;
			$organizer->description = $organizerPost->post_content;
			$organizer->slug = $organizerPost->post_name;
			$im = wp_get_attachment_image_src( $organizerMeta['_thumbnail_id'][0], 'thumbnail' );
			$imF = wp_get_attachment_image_src( $organizerMeta['_thumbnail_id'][0], 'full' );
			$organizer->image = new stdClass;
				$organizer->image->url = $im[0];
				$organizer->image->id = $organizerMeta['_thumbnail_id'][0];
				$organizer->image->width = $im[1];
				$organizer->image->height = $im[2];
			$organizer->phone = $organizerMeta['_OrganizerPhone'][0];
			$organizer->email = $organizerMeta['_OrganizerEmail'][0];
			$organizer->json_ld = new stdClass;
				$organizer->json_ld->{'@type'}= "Person";
				$organizer->json_ld->name= $organizerPost->post_title;
				$organizer->json_ld->description= $organizerPost->post_content;
				$organizer->json_ld->image = $imF[0];
				$organizer->json_ld->url = "";
				$organizer->json_ld->telephone = $organizerMeta['_OrganizerPhone'][0];
				$organizer->json_ld->email = $organizerMeta['_OrganizerEmail'][0];
				$organizer->json_ld->sameAs = "";
			$organizers[] = $organizer;
		}
		return $organizers;
	}
}