add_subdirectory( controllers )
add_subdirectory( models )

add_midas_style_test( StyleTestsSlideatlasControllers ${CMAKE_SOURCE_DIR}/modules/slideatlas/tests/controllers )
add_midas_style_test( StyleTestsSlideatlasModels ${CMAKE_SOURCE_DIR}/modules/slideatlas/tests/models )
