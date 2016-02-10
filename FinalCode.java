import java.io.*;
import java.math.*;
import java.util.regex.*;
import org.apache.hadoop.conf.Configuration;
import org.apache.hadoop.fs.*;
import java.util.*;
import java.net.URI;
import org.apache.hadoop.fs.FileStatus;
import org.apache.hadoop.io.LongWritable;
import org.apache.hadoop.io.DoubleWritable;
import org.apache.hadoop.io.WritableComparable;
import org.apache.hadoop.io.WritableComparator;
import org.apache.hadoop.io.Text;
import org.apache.hadoop.mapreduce.Job;
import org.apache.hadoop.mapreduce.Mapper;
import org.apache.hadoop.mapreduce.Partitioner;
import org.apache.hadoop.mapreduce.Reducer;
import org.apache.hadoop.mapreduce.lib.input.KeyValueTextInputFormat;
import org.apache.hadoop.mapreduce.lib.input.FileInputFormat;
import org.apache.hadoop.mapreduce.lib.input.TextInputFormat;
import org.apache.hadoop.mapreduce.lib.output.FileOutputFormat;
import org.apache.hadoop.mapreduce.lib.output.TextOutputFormat;

public class TexasWeather
{ // 2 mapper are used here. followed by 2 reducers.
	public static class Mapper1 extends Mapper<LongWritable, Text, Text, Text>
	{
		@Override
		public void map(LongWritable key, Text value, Context context) throws IOException, InterruptedException
	  { // get the string and split it. as the input it split on space, we must use " " as the input to the split function.
	    	String inputRead = value.toString();//splitting the input based on space.
				String[] inputStringSplit = inputRead.split("  *");
				// variables for capturing the tempertaure, dewpoint and windspeed for every line.
				double temperature, dewpoint, windspeed;
				int hour,month;
				String changedKey,changedValue,daySection;
				String[] listOfDates;

				temperature = Double.parseDouble(inputStringSplit[3]);
					if ( temperature == 9999.9 )
						changedValue = "NullValue"; // N here specifies that there is no value in here. default is said to be 9999.9
					else
						changedValue = inputStringSplit[3];
						changedKey = inputStringSplit[0];

					dewpoint = Double.parseDouble(inputStringSplit[4]);
					if ( dewpoint == 9999.9 )
						changedValue = changedValue + "||NullValue";
					else
						changedValue = changedValue + "||" + inputStringSplit[4];

					windSpeed = Double.parseDouble(inputStringSplit[12]);
					if ( windSpeed == 999.9 )
						changedValue = changedValue + "||NullValue";
					else
						changedValue = changedValue + "||" + inputStringSplit[12];


				listOfDates = inputStringSplit[2].split("_");
				hour = Integer.parseInt(listOfDates[1]);
				if (hour >= 5 && hour < 11)
					daySection = "Sect1";
				else if ( hour >= 11 && hour < 17)
					daySection = "Sect2";
				else if ( hour >= 17 && hour < 23)
					daySection = "Sect3";
				else
					daySection = "Sect4";

				month = ((listOfDates[0].charAt(4)-48)*10) + (listOfDates[0].charAt(5)-48);

				changedValue = inputStringSplit[0] + "_" + month + "_" + daySection;

   			context.write(new Text(changedKey), new Text(changedValue));


    }
  }

	public static class Reducer1 extends Reducer<Text, Text, Text, Text>
	{
		@Override
	  public void reduce(Text key, Iterable<Text> values, Context context) throws IOException, InterruptedException
		{
			double temp_total = 0,dew_point_total = 0,wind_speed_total = 0;
			int temp_count = 0 , dew_point_count = 0,wind_speed_count = 0;
			double temp_avg_sect = 0, dew_point_avg_sect = 0, wind_speed_avg_sect = 0;

			for (Text val : values)
			{
					String input_val = val.toString();
					String[] red1_values = input_val.split("\\||");
					if (!red1_values[0].equals("NullValue"))
					{
						temp_total += Double.parseDouble(red1_values[0]);
						temp_count++;
					}
					if (!red1_values[1].equals("NullValue"))
					{
						dew_point_total += Double.parseDouble(red1_values[1]);
						dew_point_count++;
					}
					if (!red1_values[2].equals("NullValue"))
					{
						wind_speed_total += Double.parseDouble(red1_values[2]);
						wind_speed_count++;
					}
			}
			if(temp_count != 0)
				temp_avg_sect = Math.floor(temp_total*10000/temp_count)/10000; // to check for the accuracy upto 4 digits.
			if(dew_point_count != 0)
				dew_point_avg_sect = Math.floor(dew_point_total*10000/dew_point_count)/10000;
			if(wind_speed_count != 0)
				wind_speed_avg_sect = Math.floor(wind_speed_total*10000/wind_speed_count)/10000;

			String reducer1_value = temp_avg_sect + "||" + dew_point_avg_sect + "||" + wind_speed_avg_sect;

			context.write(key, new Text(reducer1_value));
		}
	}

	public static class Stage2Mapper extends Mapper<Text, Text, Text, Text>
	{
		@Override
		public void map(Text key, Text value, Context context) throws IOException, InterruptedException
	  {
	    	String inputStringSplit2 = key.toString();
				String[] inputStringSplit2 = str.split("_"); // splitting the key passed from the Reducer1
				String chagedKey = inputStringSplit2[1] + "_" + inputStringSplit2[0];
				String changedValue = inputStringSplit2[2] + "_" + value.toString();
				System.out.println(changedKey + " " + changedValue);
   			context.write(new Text(chagedKey), new Text(changedValue));
    }
  }

	public static class Reducer2 extends Reducer<Text, Text, Text, Text>
	{
		@Override
    public void reduce(Text key, Iterable<Text> values, Context context) throws IOException, InterruptedException
		{
			String[] sect_value = new String[4];
			String input_value;
			String[] value_list;
			int counter = 4;
			for (Text val : values)
			{
				input_value = val.toString();
				value_list = input_value.split("_");
				sect_value[value_list[0].charAt(1)-49] = value_list[1];
			}

			String reducer2_value = sect_value[0];
			for (int i = 1; i < counter; i++)
				reducer2_value = reducer2_value + "||" + sect_value[i];

 			context.write(key, new Text(reducer2_value));
		}
	}

	public static void main(String[] args) throws Exception
	{
		 // code from wordCount program.
		Configuration conf = new Configuration();
		Job job1 = new Job(conf);
		FileSystem fs = FileSystem.get(conf);
		Path inputPath = new Path(args[0]);
		Path intermediateOutput1 = new Path(args[1] + "_Intermediate_Output1");
		Path finalOutput = new Path(args[1] + "_finalOutput");
		job1.setJobName("Map-Reduce 1");
		job1.setJarByClass(TexasWeather.class);
		job1.setMapOutputKeyClass(Text.class);
		job1.setMapOutputValueClass(Text.class);
		job1.setOutputKeyClass(Text.class);
		job1.setOutputValueClass(Text.class);

		job1.setInputFormatClass(TextInputFormat.class);
		job1.setOutputFormatClass(TextOutputFormat.class);
		job1.setMapperClass(Mapper1.class);
		job1.setReducerClass(Reducer1.class);
		FileInputFormat.addInputPath(job1, inputPath);
		if (fs.exists(intermediateOutput1))
				fs.delete(intermediateOutput1,true);
		FileOutputFormat.setOutputPath(job1, intermediateOutput1);
		job1.waitForCompletion(true);

		Job job2 = new Job(conf, "Map-Reduce 2");
			job2.setJarByClass(TexasWeather.class);
			job2.setMapperClass(Mapper2.class);
			job2.setReducerClass(Reducer2.class);
			job2.setOutputKeyClass(Text.class);
			job2.setOutputValueClass(Text.class);
			job2.setInputFormatClass(KeyValueTextInputFormat.class);
			KeyValueTextInputFormat.setInputPaths(job2, intermediateOutput1);

			job2.setOutputFormatClass(TextOutputFormat.class);
			if (fs.exists(finalOutput))
				fs.delete(finalOutput,true);
			FileOutputFormat.setOutputPath(job2, finalOutput);

			job2.waitForCompletion(true);

		System.exit(code);
 	}
}
