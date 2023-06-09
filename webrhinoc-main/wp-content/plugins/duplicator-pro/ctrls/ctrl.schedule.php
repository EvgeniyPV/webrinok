<?php

defined("ABSPATH") or die("");
if (!class_exists('DUP_PRO_CTRL_Schedule')) :
    class DUP_PRO_CTRL_Schedule extends DUP_PRO_Web_Services
    {
        public function __construct()
        {
            /* Schedule Options */
            $this->add_class_action('wp_ajax_duplicator_pro_schedule_bulk_delete', 'duplicator_pro_schedule_bulk_delete');
            $this->add_class_action('wp_ajax_duplicator_pro_get_schedule_infos', 'get_schedule_infos');
            $this->add_class_action('wp_ajax_duplicator_pro_run_schedule_now', 'run_schedule_now');
        }

        function duplicator_pro_schedule_bulk_delete()
        {
            DUP_PRO_Handler::init_error_handler();
            check_ajax_referer('duplicator_pro_schedule_bulk_delete', 'nonce');

            $json      = array(
            'success' => false,
            'message' => '',
            );
            $isValid   = true;
            $inputData = filter_input_array(INPUT_POST, array(
            'schedule_ids' => array(
                'filter'  => FILTER_VALIDATE_INT,
                'flags'   => FILTER_REQUIRE_ARRAY,
                'options' => array(
                    'default' => false
                )
            )
            ));
            $scheduleIDs  = $inputData['schedule_ids'];
            $delCount  = 0;

            if (!$scheduleIDs || empty($scheduleIDs) || in_array(false, $scheduleIDs)) {
                $isValid = false;
            }


            try {
                DUP_PRO_U::hasCapability('export', DUP_PRO_U::SECURE_ISSUE_THROW);

                if (!$isValid) {
                    DUP_PRO_Log::trace("Inv");
                    throw new Exception(DUP_PRO_U::__("Invalid Request."));
                }

                foreach ($scheduleIDs as $id) {
                    $schedule = DUP_PRO_Schedule_Entity::delete_by_id($id);
                    if ($schedule) {
                        $delCount++;
                    }
                }

                $json['success'] = true;
                $json['ids']     = $scheduleIDs;
                $json['removed'] = $delCount;
            } catch (Exception $ex) {
                $json['message'] = $ex->getMessage();
            }

            die(json_encode($json));
        }

        // return schedule status'
        // { schedule_id, is_running=true|false, last_ran_string}
        function get_schedule_infos()
        {
            DUP_PRO_Handler::init_error_handler();
            check_ajax_referer('duplicator_pro_get_schedule_infos', 'nonce');
            DUP_PRO_U::hasCapability('export');
            $schedules      = DUP_PRO_Schedule_Entity::get_all();
            $schedule_infos = array();

            if (count($schedules) > 0) {
                $package = DUP_PRO_Package::get_next_active_package();

                foreach ($schedules as $schedule) {
                    /* @var $schedule DUP_PRO_Schedule_Entity */
                    $schedule_info = new stdClass();

                    $schedule_info->schedule_id     = $schedule->id;
                    $schedule_info->last_ran_string = $schedule->get_last_ran_string();

                    if ($package != null) {
                        $schedule_info->is_running = ($package->schedule_id == $schedule->id);
                    } else {
                        $schedule_info->is_running = false;
                    }

                    array_push($schedule_infos, $schedule_info);
                }
            }

            $json_response = json_encode($schedule_infos);
            die($json_response);
        }

        function run_schedule_now()
        {
            DUP_PRO_Handler::init_error_handler();
            check_ajax_referer('duplicator_pro_run_schedule_now', 'nonce');

            $json        = array(
                'success' => false,
                'message' => '',
            );

            try {
                DUP_PRO_U::hasCapability('export', DUP_PRO_U::SECURE_ISSUE_THROW);
                $schedule_id = filter_input(INPUT_POST, 'schedule_id', FILTER_VALIDATE_INT);

                if ($schedule_id === false) {
                    throw new Exception(DUP_PRO_U::__("Invalid schedule id"));
                }

                $schedule = DUP_PRO_Schedule_Entity::get_by_id($schedule_id);

                if ($schedule == null) {
                    DUP_PRO_LOG::trace("Attempted to queue up a job for non existent schedule $schedule_id");
                    throw new Exception(DUP_PRO_U::__("Invalid schedule id"));
                }

                DUP_PRO_LOG::trace("Inserting new package for schedule $schedule->name due to manual request");
                // Just inserting it is enough since init() will automatically pick it up and schedule a cron in the near future.
                $schedule->insert_new_package(true);
                DUP_PRO_Package_Runner::kick_off_worker();

                $json        = array(
                    'success' => true,
                    'message' => '',
                );
            } catch (Exception $e) {
                $json['success'] = false;
                $json['message'] = $e->getMessage();
            } catch (Error $e) {
                $json['success'] = false;
                $json['message'] = $e->getMessage();
            }

            die(json_encode($json));
        }
    }
endif;
