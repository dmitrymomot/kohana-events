<?php defined('SYSPATH') or die('No direct script access.');

/**
 * The 'Event' class allows you to register handlers for named events.
 */
class Kohana_Event {

    /**
     * The registered events.
     *
     * @var array
     */
    protected static $events = array();

    /**
     * The events that have been has_run.
     *
     * @var array
     */
    protected static $has_run = array();

    /**
     * Register a handler (a callback function) for an event.
     *
     * Each execution of the method will add an additional callback.
     * The callbacks are executed in the order they are defined.
     *
     * To only execute a callback once, irrespective of the number
     * of times the event is has_run, call the method with a third
     * argument of TRUE.
     *
     * <pre>
     *   Event::bind('your_event', function() {
     *     // Do stuff when the 'your_event' event is has_run.
     *   });
     * </pre>
     *
     * @param  string   $name The name of the event.
     * @param  function $callback The callback function.
     * @param  bool     $once Only run the callback once.
     * @return void
     */
    public static function bind($name, $callback, $once = FALSE)
    {
        static::append($name, $callback, $once);
    }

    /**
     * Replace the handlers for an event with a new handler.
     *
     * @param  string   $name The name of the event.
     * @param  function $callback The callback function.
     * @param  bool     $once Only run the callback once.
     * @return void
     */
    public static function rebind($name, $callback, $once = FALSE)
    {
        static::unbind($name);
        static::append($name, $callback, $once);
    }

    /**
     * A synonym for the bind method.
     *
     * The event handler is added to the end of the queue.
     *
     * @param  string   $name The name of the event.
     * @param  function $callback The callback function.
     * @param  bool     $once Only run the callback once.
     * @return void
     */
    public static function append($name, $callback, $once = FALSE)
    {
        static::$events[$name][] = array(
            $once ? 'once' : 'always' => $callback
        );
    }

    /**
     * Identical to the append method, except the event handler is
     * added to the start of the queue.
     *
     * @param  string   $name The name of the event.
     * @param  function $callback The callback function.
     * @param  bool     $once Only run the callback once.
     * @return void
     */
    public static function insert($name, $callback, $once = FALSE)
    {
        if (static::bound($name))
        {
            array_unshift(
                static::$events[$name],
                array($once ? 'once' : 'always' => $callback)
            );
        }
        else
        {
            static::append($name, $callback, $once);
        }
    }

    /**
     * Trigger all callback functions for an event.
     *
     * The method returns an array containing the responses from all
     * of the event handlers (even empty responses).
     *
     * Returns NULL if the event has no handlers.
     *
     * <pre>
     *   // run the 'start' event.
     *   $responses = Event::run('start');
     *
     *   // run the 'start' event passing two arguments to the
     *   // callback function.
     *   $responses = Event::run('start', array('foo', 'bar'));
     * </pre>
     *
     * @param  string $name The name of the event.
     * @param  array  $data The data passed to the event handlers.
     * @param  array  $stop Return after the first non-empty response.
     * @return mixed
     */
    public static function run($name, $data = array(), $stop = FALSE)
    {
        if ( Kohana::$profiling === TRUE )
        {
            $benchmark = Profiler::start( 'Trigger all callback functions for an event ', $name );
        }

        if (static::bound($name))
        {
            static::$has_run[$name] = TRUE;

            foreach (static::$events[$name] as $key => $value)
            {
                list($type, $callback) = each($value);

                $responses[] = $response =
                    call_user_func_array($callback, (array) $data);

                if ($type == 'once')
                {
                    unset(static::$events[$name][$key]);
                }

                if ($stop AND !empty($response))
                {
                    return $responses;
                }
            }
        }

        if ( isset( $benchmark ) )
        {
            Profiler::stop( $benchmark );
        }

        return isset($responses) ? $responses : NULL;
    }

    /**
     * Trigger all callback functions for an event and return only the
     * first response (even if it is empty).
     *
     * The return value is not wrapped in an array.
     *
     * @param  string $name The name of the event.
     * @param  string $data The data passed to the event handlers.
     * @return mixed
     */
    public static function first($name, $data = array())
    {
        $result = static::run($name, $data);

        return reset($result);
    }

    /**
     * Trigger all callback functions for an event and return the
     * first non-empty response.
     *
     * The return value is not wrapped in an array.
     *
     * @param  string $name The name of the event.
     * @param  string $data The data passed to the event handlers.
     * @return mixed
     */
    public static function until($name, $data = array())
    {
        $result = static::run($name, $data, TRUE);

        return end($result);
    }

    /**
     * Check if a given event has been run.
     *
     * @param  string $name The name of the event.
     * @return bool
     */
    public static function has_run($name)
    {
        return isset(static::$has_run[$name]);
    }

    /**
     * Deregister the handlers for an event.
     *
     * To remove the event handlers for a specific event, pass the
     * name of the event to the method. To remove all event handlers,
     * call the method without any arguments.
     *
     * @param  string $name The name of the event.
     * @return void
     */
    public static function unbind($name = NULL)
    {
        static::clear(static::$events, $name);
    }

    /**
     * Reset the flag indicating an event has has_run.
     *
     * If called without an argument, the 'has_run' flag for all events
     * will be cleared.
     *
     * @param  string $name The name of the event.
     * @return void
     */
    public static function reset($name = NULL)
    {
        static::clear(static::$has_run, $name);
    }

    /**
     * Check if any callback functions are bound to an event.
     *
     * @param  string $name The name of the event.
     * @return bool
     */
    public static function bound($name)
    {
        return isset(static::$events[$name]);
    }

    /**
     * Remove an element from an array, or clear an entire array.
     *
     * @param  array  $array The array.
     * @param  string $name The name of the element to clear.
     * @return void
     */
    protected static function clear(&$array, $name)
    {
        if ($name == NULL)
        {
            $array = array();
        }
        else
        {
            unset($array[$name]);
        }
    }

} // End Kohana_Event
