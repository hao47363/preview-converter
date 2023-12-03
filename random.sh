#!/bin/bash

if [ "$#" -ne 2 ]; then
    echo "Usage: $0 <output_file_name> <M3U8_URL>"
    exit 1
fi

output_file="$1"
m3u8_url="$2"
concat_list="concat_list.txt"

# Create a temporary folder to store the selected video segments
mkdir -p "${output_file}"

# Extract domain from the provided M3U8 URL
domain=$(echo "$m3u8_url" | awk -F/ '{print $3}')

# Extract segment URLs from the provided M3U8 playlist
segment_urls=($(curl -s "${m3u8_url}" | grep -E -o "[a-zA-Z0-9./?=_-]*.ts"))

# Calculate the interval for selecting segments
total_segments=${#segment_urls[@]}
interval=$((total_segments / 8))
echo "total segment: ${total_segments} and interval: ${interval}"
# Create a temporary file to store the list of selected videos
# echo "file '${output_file}/segment_0.ts'" > "${concat_list}"

# Download and save one second from each interval
for i in {0..7}
do
  selected_index=$((i * interval))
  segment_url="${segment_urls[$selected_index]}"
  echo "segment_url:${segment_url}"
  ffmpeg -i "https://${domain}${segment_url}" -t 1 -an -c:v copy "${output_file}/segment_${i}.ts"
  echo "file '${output_file}/segment_${i}.ts'" >> "${concat_list}"
done

# Use the concat demuxer to combine the selected videos
ffmpeg -f concat -safe 0 -i "${concat_list}" -c:v copy "${output_file}.mp4"

# Remove the temporary folder and file
rm -r "${output_file}"
rm "${concat_list}"
