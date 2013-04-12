Events
======

Events module for Kohana 3.3


Uses
-----
### Bind

    Event::bind('my_event', function() {
        return 'The event 'my_event' was called.';
    });

    Event::bind('my_event', function() {
        return 'The event 'my_event' was logged.';
    });

### Run
    // Trigger the 'my_event' event:
    $responses = Event::run('my_event');

    // Result:
    $responses = array(
        'The event 'my_event' was called.',
        'The event 'my_event' was logged.'
    );

To only execute a callback once, irrespective of the number of times the event is called, when registering an event handler, call the ``bind`` method with a third argument of TRUE:

    // Only execute this handler once.
    Event::bind('my_event', function() {
        return 'The event 'my_event' was called.';
    }, TRUE);

    // Execute this handler every time.
    Event::bind('my_event', function() {
        // Log data...
        return 'Event data was logged.';
    });

Now if the event is run twice, the first handler will only be called once:

    // Trigger the 'my_event' event twice:
    $one = Event::run('my_event');
    $two = Event::run('my_event');

    // Results:
    $one = array(
        'The event 'my_event' was called.',
        'Event data was logged.'
    );

    $two = array(
        'Event data was logged.'
    );

You can easily pass data to the registered callback functions when firing an event:

    // Register a handler that expects two parameters:
    Event::bind('log', function($log, $data) {
        // Write $data to a $log.
    });

    // Pass data to the event handler:
    Event::run('log', array('log.txt', 'Something happened.'));

### Append and Insert
The ``append`` method is an alias for the ``bind`` method; both add event handlers to the end of the list of existing handlers.

The ``insert`` method takes the same parameters as ``bind``, but adds the event handler to the start of the queue, ensuring it is executed before any of the other callback functions.

### First and Until
The ``first`` method triggers all callback functions for an event and returns only the first response (even if it is empty). As the method returns only a single value, the result is not wrapped in an array.

    // Returns: 'The event 'my_event' was called.'
    $response = Event::first('my_event');

The ``until`` method is very similar to the ``first`` method, but it returns the first non-empty response.

### Bound, Has_Run and Reset
Use the ``bound`` method to check if any handlers are registered for an event:

    // Returns: TRUE.
    $bound = Event::bound('my_event');

Use the ``has_run`` method to check if an event has run:

    // Run the event:
    Event::run('my_event');

    // Returns: TRUE.
    $has_run = Event::has_run('my_event');

Use the ``reset`` method to clear the flag indicating an event has run. If called without an argument, all event counters are reset.

    // Clear the 'run' count for the event:
    Event::reset('my_event');

    // Returns: FALSE.
    $times = Event::has_run('my_event');

### Rebind and Unbind
The ``rebind`` method takes the same parameters as ``bind``, but it will overwrite all existing handlers defined for an event with a new callback function.

The ``unbind`` method deregisters the handlers for an event:

    // Remove all callback functions bound to the event:
    Event::unbind('my_event');

    // Returns NULL as all handlers have been removed:
    Event::run('my_event');

If called without an argument, the ``unbind`` method will remove the handlers for all events.
